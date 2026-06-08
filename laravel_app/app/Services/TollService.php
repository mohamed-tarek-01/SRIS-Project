<?php

namespace App\Services;

use App\Models\TrafficRecord;
use App\Models\User;
use App\Models\TollTransaction;
use Illuminate\Support\Facades\DB;

class TollService
{
    /**
     * Process a toll payment based on a detected plate.
     *
     * @param  string  $plateText
     * @param  int|null  $stationId
     * @param  float   $amount
     * @return array
     */
    public function processToll(string $plateText, ?int $stationId = null): array
    {
        // 1. Resolve Station Info
        $station = $stationId ? \App\Models\Station::find($stationId) : null;
        $stationName = $station ? $station->name : 'Manual Entry Station';
        // Base amount, will be overridden later based on vehicle_type
        $amount = 10.00;

        // 2. DB Cooldown: same plate at same station within 2 hours → skip
        $recentlyCharged = \App\Models\TollTransaction::where('plate_number', $plateText)
            ->where('station_id', $stationId)
            ->where('created_at', '>=', now()->subHours(2))
            ->exists();

        if ($recentlyCharged) {
            return [
                'status'  => 'duplicate',
                'message' => "Plate [{$plateText}] was already processed at [{$stationName}] within the last 2 hours.",
            ];
        }

        // 3. Find the Traffic Record — fuzzy match (Levenshtein ≤ 2) to handle OCR errors
        //    e.g. OCR reads 'سف٩١٥٨' but the real plate is 'سأف٩١٥٨'
        $trafficRecord = $this->findBestTrafficRecord($plateText);

        if (!$trafficRecord) {
            return [
                'status' => 'unregistered',
                'message' => "Plate [{$plateText}] not found in National Traffic Records.",
            ];
        }

        // Determine amount based on vehicle type
        $amount = ($trafficRecord->vehicle_type === 'commercial') ? 20.00 : 10.00;

        // 3. Find the User associated with this National ID
        $user = \App\Models\User::where('national_id', $trafficRecord->national_id)->first();

        if (!$user) {
            return [
                'status' => 'not_linked',
                'message' => "Vehicle identified, but no system account is linked to National ID [{$trafficRecord->national_id}].",
            ];
        }

        // 4. Process the transaction
        return \Illuminate\Support\Facades\DB::transaction(function () use ($user, $plateText, $amount, $stationName, $stationId) {
            $status = 'success';
            $message = "Automated toll of {$amount} Points successfully deducted from {$user->name}'s wallet at [{$stationName}].";

            if ($user->balance < $amount) {
                $status = 'failed';
                $message = "Insufficient points in {$user->name}'s wallet. A fine of 1000 EGP has been issued at [{$stationName}].";
                
                // Create Fine Record
                \App\Models\Fine::create([
                    'user_id' => $user->id,
                    'station_id' => $stationId,
                    'plate_number' => $plateText,
                    'amount' => 1000.00,
                    'reason' => "Insufficient points for Toll Gate: {$stationName}",
                    'status' => 'pending',
                ]);
            } else {
                $user->decrement('balance', $amount);
            }

            $transaction = \App\Models\TollTransaction::create([
                'user_id' => $user->id,
                'station_id' => $stationId,
                'plate_number' => $plateText,
                'amount' => $amount,
                'station_name' => $stationName,
                'status' => $status,
            ]);

            return [
                'status' => $status,
                'message' => $message,
                'station' => $stationName,
                'user_name' => $user->name,
                'remaining_balance' => $user->balance,
                'transaction_id' => $transaction->id,
            ];
        });
    }

    /**
     * Find the single best-matching TrafficRecord for a given OCR plate text.
     *
     * Uses PHP's levenshtein() to handle OCR errors like dropped characters
     * (e.g. 'سف٩١٥٨' → finds 'سأف٩١٥٨') or confused characters (س ↔ ش).
     *
     * Only returns a match if the Levenshtein distance is ≤ 2.
     * If multiple records are within that threshold, the CLOSEST one wins.
     * If there's a tie (two plates equally close), no match is returned to
     * avoid charging the wrong person.
     */
    private function findBestTrafficRecord(string $plateText): ?\App\Models\TrafficRecord
    {
        // First: try exact match (cheapest)
        $exact = \App\Models\TrafficRecord::where('plate_number', $plateText)->first();
        if ($exact) {
            return $exact;
        }

        // Fuzzy search — load all plates and compute Levenshtein in PHP
        $all = \App\Models\TrafficRecord::select('id', 'plate_number', 'national_id', 'vehicle_type')->get();

        $bestRecord  = null;
        $bestDist    = 3;          // only accept distance ≤ 2
        $tieDetected = false;

        foreach ($all as $record) {
            $dist = levenshtein($plateText, $record->plate_number);
            if ($dist < $bestDist) {
                $bestDist    = $dist;
                $bestRecord  = $record;
                $tieDetected = false;
            } elseif ($dist === $bestDist && $bestDist <= 2) {
                // Two plates are equally close → ambiguous → reject both
                $tieDetected = true;
            }
        }

        return $tieDetected ? null : $bestRecord;
    }
}
