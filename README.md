# Smart Road Integrated System (SRIS) 🚦🤖

![SRIS Cover](laravel_app/public/images/logo.png) <!-- Update with actual logo/cover path if available -->

**SRIS (Smart Road Integrated System)** is an advanced, AI-powered platform designed to transform reactive road infrastructure management into a proactive, intelligent, and real-time ecosystem. By unifying multiple Deep Learning models with a robust web application, SRIS automates traffic monitoring, hazard detection, automated toll collection, and road maintenance reporting.

🌐 **Live Deployment (Google Cloud):** [https://sris-project.duckdns.org](https://sris-project.duckdns.org)

---

## 🌟 Key Features

SRIS integrates **9 distinct AI models** into a seamless, real-time ecosystem:

1. 🚗 **License Plate Recognition (OCR):** Automated toll collection and detection of unregistered or fake plates.
2. 💥 **Accident Detection:** Real-time identification of vehicle collisions, categorized by severity (Moderate/Severe).
3. 🛣️ **Surface Defect Detection:** Automatic detection of longitudinal cracks, potholes, and road degradation.
4. 🔥 **Fire & Smoke Detection:** Early warning system for vehicle fires or environmental hazards.
5. 🛡️ **Car Damage Assessment:** AI evaluation of vehicle damage post-accident for insurance and traffic reporting.
6. 🛑 **Traffic Sign Recognition:** Detection and classification of road signs.
7. 🚙 **Vehicle Type Classification:** Categorizes vehicles (e.g., car, truck, bus) for traffic analysis.

### 👥 User Features (Drivers & Citizens)
- **Gamification & Rewards:** Users earn points by reporting road hazards (e.g., cracks, accidents) which can be used to pay off traffic fines.
- **Traffic Utilities:** View personal violation history, traffic fines reference, and toll records.
- **Live Camera Support:** Direct integration with smartphone cameras (via secure HTTPS) to capture and analyze hazards on the go.

### 🛡️ Admin Features (Authorities)
- **Live GIS Dashboard:** Interactive map displaying real-time alerts with exact GPS coordinates.
- **Neural Gates:** Configuration of monitoring stations (simulated CCTVs) across the city.
- **Verification Portal:** Review user-submitted hazard reports and payment receipts to approve and grant reward points.

---

## 🛠️ Technology Stack

SRIS leverages a decoupled, containerized microservices architecture:

- **Frontend & Core Backend:** [Laravel 11](https://laravel.com/) (PHP), Blade, Tailwind CSS, Alpine.js
- **AI & Machine Learning API:** [FastAPI](https://fastapi.tiangolo.com/) (Python), PyTorch, YOLO, OpenCV
- **Database:** MySQL 8
- **DevOps & Deployment:** Docker Compose, Google Cloud Platform (Compute Engine), Nginx, Let's Encrypt (SSL)

---

## 🏗️ Architecture & Infrastructure

```text
                        Internet (Users)
                              │
                              ▼
                 ┌──────────────────────────┐
                 │    Google Cloud VM       │
                 │ (sris-project.duckdns.org│
                 └────────────┬─────────────┘
                              │
          ┌───────────────────┴───────────────────┐
          │               Docker Engine           │
          │                                       │
          │  ┌─────────┐             ┌─────────┐  │
          │  │  Nginx  │◄──(Port 80)─│ Laravel │  │
          │  │ :80/443 │             │  :9000  │  │
          │  └─────────┘             └─────────┘  │
          │                               │       │
          │  ┌─────────┐             ┌─────────┐  │
          │  │  MySQL  │◄──(Private)─│   ML    │  │
          │  │  :3306  │             │ Service │  │
          │  └─────────┘             └─────────┘  │
          └───────────────────────────────────────┘
```
- **Environment Parity:** Docker Compose guarantees that the local development environment is perfectly identical to the Google Cloud production environment.
- **Network Isolation:** The database container is entirely hidden from the public internet, accessible only within the internal Docker bridge network.
- **Security:** Strict CORS middleware ensures the FastAPI ML Service only accepts requests from the trusted Laravel frontend.

---

## 🚀 Local Installation & Setup

To run SRIS on your local machine, you will need **Docker** and **Docker Compose** installed.

### 1. Clone the repository
```bash
git clone https://github.com/mohamed-tarek-01/SRIS-Project.git
cd SRIS-Project
```

### 2. Configure Environment Variables
Copy the example environment files and configure them if necessary (default values work out of the box for Docker).
```bash
cp laravel_app/.env.example laravel_app/.env
```
*(Ensure `DB_HOST=db` and `ML_SERVICE_URL=http://ml_service:8000` in the `.env` file).*

### 3. Build and Start Containers
```bash
docker-compose up --build -d
```

### 4. Setup Laravel
Run the migrations and seed the database with initial data (admin accounts, default stations).
```bash
docker-compose exec laravel composer install
docker-compose exec laravel php artisan key:generate
docker-compose exec laravel php artisan migrate --seed
docker-compose exec laravel php artisan storage:link
```

### 5. Access the Application
- **Main Website:** [http://localhost](http://localhost)
- **Database Adminer:** [http://localhost:8080](http://localhost:8080)
- **ML Service API Docs:** [http://localhost:8001/docs](http://localhost:8001/docs)

*Note: For the ML models to work fully, the respective `.pth` weights must be placed in the `ml_service/models/` directories (excluded from git).*

---

## 🗺️ Future Roadmap

- **Live CCTV Integration:** RTSP feed support for direct connection to municipal camera networks without manual video upload.
- **Edge Computing:** Deployment of lightweight ML models on roadside hardware to achieve sub-100ms detection latency.
- **Emergency API:** Direct, automated API hooks to ambulance dispatchers and police stations upon detection of severe accidents.

---
*Developed as a Graduation Project.*
