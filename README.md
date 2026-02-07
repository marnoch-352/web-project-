# Cardiac Rehab Exercise Tracking System

ระบบติดตามการออกกำลังกายสำหรับผู้ป่วยโรคหัวใจ (Cardiac Rehabilitation System)

## 👥 Team Members

```
67026225 โนชมานิต โกสม
67021781 ธัชกร แย้มสังข์
67022209 ศรรวริชญ์ นิยมสัตย์
67021983 พัชรพล วราโภค
```

## 🚀 Quick Start

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- Web Browser (Chrome, Firefox, Edge)
- Git (for cloning)

### Installation

1. **Clone Repository**
   ```bash
   git clone https://github.com/marnoch-352/web-project-.git
   cd cardiac_final
   ```

2. **Start XAMPP**
   - เปิด XAMPP Control Panel
   - Start **Apache** และ **MySQL**

3. **Create Database**
   - เปิด phpMyAdmin: `http://localhost/phpmyadmin`
   - สร้าง database ชื่อ `cardiac_rehab`
   - Import SQL files ตามลำดับ:
     1. `backend/database_setup.sql`
     2. `backend/update_patients_table.sql`
     3. `backend/create_exercise_tables.sql`

4. **Configure Database Connection**
   - เปิดไฟล์ `backend/db_config.php`
   - ตรวจสอบการตั้งค่า (ค่าเริ่มต้นสำหรับ XAMPP):
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'cardiac_rehab');
     define('DB_USER', 'root');
     define('DB_PASS', ''); // Empty for XAMPP
     ```

5. **Run Application**
   - วางโฟลเดอร์ `cardiac_final` ไว้ใน `C:\xampp\htdocs\`
   - เปิดเบราว์เซอร์: `http://localhost/cardiac_final/frontend/index.html`

## 🔑 Demo Credentials

### Staff Login
- **Doctor:**
  - Username: `dr.smith`
  - Password: `password123`

- **Physical Therapist:**
  - Username: `pt.johnson`
  - Password: `password123`

### Patient Login
- Username: `เบอร์โทรศัพท์` (phone number)
- Password: `เลขบัตรประชาชน 13 หลัก` (national ID)

## 📁 Project Structure

```
cardiac_final/
├── backend/
│   ├── api/                    # API endpoints
│   │   ├── login.php          # Authentication
│   │   ├── add_patient.php    # Patient management
│   │   ├── get_sessions.php   # Exercise sessions
│   │   └── ...
│   ├── db_config.php          # Database configuration
│   ├── database_setup.sql     # Initial database schema
│   ├── update_patients_table.sql
│   └── create_exercise_tables.sql
├── frontend/
│   ├── html/                  # HTML pages
│   │   ├── Doctor_dashboard.html
│   │   ├── PT_dashboard.html
│   │   ├── Patient_dashboard.html
│   │   └── ...
│   ├── css/                   # Stylesheets
│   ├── javascript/            # Client-side scripts
│   └── index.html            # Landing page
└── README.md
```

## ✨ Features

### For Doctors
- ✅ เพิ่มข้อมูลผู้ป่วยใหม่
- ✅ ดูรายชื่อผู้ป่วยทั้งหมด
- ✅ ค้นหาผู้ป่วย
- ✅ ดูประวัติการออกกำลังกาย
- ✅ ดูกราฟแสดงผลการออกกำลังกาย

### For Physical Therapists
- ✅ บันทึกผลการออกกำลังกาย
- ✅ อัพโหลดภาพ EKG
- ✅ บันทึก vital signs (HR, BP, METs)
- ✅ ให้คำแนะนำสำหรับครั้งถัดไป

### For Patients
- ✅ ดูประวัติการออกกำลังกายของตัวเอง
- ✅ ดูกราฟความก้าวหน้า
- ✅ ดูคำแนะนำจากนักกายภาพ

## 🔧 Database Configuration

### Local Development (XAMPP)
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cardiac_rehab');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### For Deployment
แก้ไขไฟล์ `backend/db_config.php` ตามการตั้งค่าของ hosting ที่ใช้

## 🛠️ Technology Stack

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Libraries:** Chart.js (for graphs)

## 📊 Database Tables

- `users` - ข้อมูลหมอและนักกายภาพ
- `patients` - ข้อมูลผู้ป่วย
- `exercise_sessions` - บันทึกการออกกำลังกาย

## 🔒 Security Features

- ✅ Password hashing (bcrypt)
- ✅ Session management
- ✅ Role-based access control
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ Input validation

## 📝 Notes

- รหัสผ่านเริ่มต้นสำหรับ demo: `password123`
- ควรเปลี่ยนรหัสผ่านก่อนใช้งานจริง
- ไฟล์ `db_config.php` ถูก exclude จาก Git (ดูใน `.gitignore`)

## 🐛 Troubleshooting

### Database Connection Error
- ตรวจสอบว่า MySQL ทำงานอยู่
- ตรวจสอบชื่อ database ใน phpMyAdmin
- ตรวจสอบการตั้งค่าใน `db_config.php`

### Login Failed
- ตรวจสอบว่า import SQL files ครบทุกไฟล์
- ลองใช้ demo credentials ที่ระบุไว้ข้างต้น

### Cannot Upload EKG Images
- ตรวจสอบว่าโฟลเดอร์ `backend/uploads/ekg/` มีสิทธิ์เขียนไฟล์

## 📞 Support

หากมีปัญหาหรือข้อสงสัย ติดต่อทีมพัฒนาได้ที่ GitHub Issues

---

**Made with ❤️ by Team Cardiac Rehab**
