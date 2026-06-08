<?php

namespace App\Console\Commands;

use App\Mail\DailyCarTip;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailyCarTips extends Command
{
    protected $signature   = 'tips:send-daily';
    protected $description = 'Send a daily car maintenance tip email to all registered users.';

    // 30 rotating tips
    protected array $tips = [
        ['icon'=>'🛢️','title'=>'Change Your Oil Regularly','body'=>'Oil is your engine\'s lifeblood. Change it every 5,000–7,500 km to keep internal components lubricated and prevent costly engine wear.'],
        ['icon'=>'💨','title'=>'Check Tire Pressure Monthly','body'=>'Properly inflated tires improve fuel efficiency by up to 3% and extend tire life significantly. Check pressure when tires are cold.'],
        ['icon'=>'🔋','title'=>'Test Your Battery','body'=>'Car batteries typically last 3–5 years. Have yours tested annually, especially before winter, to avoid unexpected breakdowns.'],
        ['icon'=>'🧊','title'=>'Flush the Coolant','body'=>'Coolant prevents overheating and corrosion. Flush and replace it every 2 years or 40,000 km to maintain proper engine temperature.'],
        ['icon'=>'🫧','title'=>'Replace Air Filters','body'=>'A clogged air filter reduces engine efficiency and acceleration by up to 10%. Replace every 15,000–25,000 km for optimal performance.'],
        ['icon'=>'🛞','title'=>'Rotate Tires Every 10,000 km','body'=>'Rotating tires ensures even wear and extends their lifespan by thousands of kilometers. Don\'t skip this simple step.'],
        ['icon'=>'🔦','title'=>'Check All Lights','body'=>'Faulty lights are a safety hazard and a traffic violation. Inspect headlights, taillights, and indicators monthly.'],
        ['icon'=>'🧴','title'=>'Top Up Windshield Fluid','body'=>'Never let your washer fluid run dry. Use a quality fluid that prevents smearing and keeps your view crystal clear.'],
        ['icon'=>'⚙️','title'=>'Inspect Brake Pads','body'=>'Squealing or grinding noises indicate worn brake pads. Replace them immediately to maintain stopping power and protect rotors.'],
        ['icon'=>'🚗','title'=>'Smooth Acceleration Saves Fuel','body'=>'Aggressive acceleration and hard braking can reduce fuel efficiency by up to 40%. Smooth driving saves money and reduces wear.'],
        ['icon'=>'🌡️','title'=>'Monitor Your Temperature Gauge','body'=>'If your temperature gauge rises into the red zone, pull over immediately. Overheating can cause catastrophic engine damage.'],
        ['icon'=>'🧹','title'=>'Wash Your Car Regularly','body'=>'Regular washing removes road salt, bird droppings, and grime that cause paint and body rust. Wax every 3 months for extra protection.'],
        ['icon'=>'⚡','title'=>'Keep Spark Plugs Fresh','body'=>'Worn spark plugs cause misfires, poor fuel economy, and rough idling. Replace every 30,000–100,000 km depending on type.'],
        ['icon'=>'🔩','title'=>'Tighten the Gas Cap','body'=>'A loose gas cap triggers the check engine light and lets fuel vapors escape, wasting fuel. Always ensure it clicks shut.'],
        ['icon'=>'🏎️','title'=>'Warm Up in Cold Weather','body'=>'In winter, let your engine idle for 30–60 seconds before driving. Modern engines need this to circulate oil properly.'],
        ['icon'=>'📍','title'=>'Check Wheel Alignment','body'=>'Misaligned wheels cause uneven tire wear and poor handling. Have alignment checked annually or after hitting a major pothole.'],
        ['icon'=>'🧯','title'=>'Know Your Emergency Tools','body'=>'Keep a spare tire, jack, jumper cables, and emergency triangle in your car at all times. Preparation prevents panic.'],
        ['icon'=>'💧','title'=>'Check for Fluid Leaks','body'=>'Spots under your car are warning signs. Oil is dark brown, coolant is green/yellow, and brake fluid is clear. Investigate any leak promptly.'],
        ['icon'=>'🛡️','title'=>'Protect Against Rust','body'=>'Apply underbody coating and keep your car garaged when possible. Rust is irreversible and reduces vehicle value dramatically.'],
        ['icon'=>'🌬️','title'=>'Service Your AC Annually','body'=>'A well-maintained AC system is more efficient. Have it recharged and inspected annually to avoid expensive compressor replacements.'],
        ['icon'=>'📊','title'=>'Track Your Fuel Economy','body'=>'Monitor litres per 100km regularly. A sudden drop in efficiency often indicates engine, tire, or fuel system issues worth investigating.'],
        ['icon'=>'🔧','title'=>'Don\'t Ignore the Check Engine Light','body'=>'The check engine light is a warning, not decoration. Ignoring it can turn a minor fix into a major repair. Get it diagnosed promptly.'],
        ['icon'=>'🧲','title'=>'Check the Serpentine Belt','body'=>'The serpentine belt powers critical components. Look for cracks or fraying and replace every 60,000–100,000 km to prevent sudden failure.'],
        ['icon'=>'🌊','title'=>'Flush Brake Fluid','body'=>'Brake fluid absorbs moisture over time, lowering its boiling point. Flush every 2 years to maintain reliable stopping performance.'],
        ['icon'=>'📱','title'=>'Use a Dash Cam','body'=>'A dash cam records evidence in case of accidents and can lower insurance premiums. It\'s a low-cost, high-value investment.'],
        ['icon'=>'🌿','title'=>'Reduce Idle Time','body'=>'Idling for more than 60 seconds wastes more fuel than restarting the engine. Turn off the engine during long waits to save fuel.'],
        ['icon'=>'🎯','title'=>'Align After New Tires','body'=>'Always get a wheel alignment after installing new tires. Misalignment will cause premature wear and negate your investment.'],
        ['icon'=>'🔊','title'=>'Listen to Your Car','body'=>'Unusual sounds like knocking, squealing, or rattling are your car communicating. Address them early before they become costly problems.'],
        ['icon'=>'📅','title'=>'Follow the Service Schedule','body'=>'Your owner\'s manual has a service schedule tailored to your vehicle. Following it religiously is the single best thing you can do for longevity.'],
        ['icon'=>'🏁','title'=>'Drive Conservatively to Save Fuel','body'=>'Maintaining a steady speed on highways, using cruise control, and planning routes reduces fuel consumption significantly.'],
    ];

    public function handle(): void
    {
        $users = User::all();
        $dayOfYear = (int) now()->format('z'); // 0–365
        $tip = $this->tips[$dayOfYear % count($this->tips)];

        $sent = 0;
        foreach ($users as $user) {
            try {
                Mail::to($user->email)->send(new DailyCarTip($tip, $user->name));
                $sent++;
            } catch (\Throwable $e) {
                $this->error("Failed to send to {$user->email}: " . $e->getMessage());
            }
        }

        $this->info("Daily tip '{$tip['title']}' sent to {$sent} users.");
    }
}
