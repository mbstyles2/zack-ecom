
# ZACK - School Supplies E-commerce Platform

A complete **school supplies management system** built for African schools, parents, and students. Institutions can manage supplies, parents/students can order kits and individual items, and admins oversee the entire platform.

## Features

### 👤 User Roles
- **Admin** - Full system control
- **Institution (Schools)** - Manage school profile, request quotations, view orders
- **Parent / Guardian** - Browse products, create orders, track deliveries
- **Student** - Access school requirements and order supplies

### Core Functionalities
- User registration & login (separate flows for Admin & Users)
- Product management (CRUD)
- Kit management (Full school kits)
- Shopping cart & Checkout
- Quotation request system for schools
- Order management with status tracking
- Delivery workflow support (M-Pesa & Bank Transfer ready)
- Responsive & modern UI

## Tech Stack

- **Backend**: PHP 8+
- **Database**: MySQL / MariaDB
- **Styling**: Custom CSS + Google Fonts (Poppins)
- **Security**: PDO with prepared statements, password hashing (bcrypt)

## Installation & Setup

###. Clone / Download Project
```bash
git clone <your-repo-url>
cd zack-ecomm
```

###. Database Setup
1. Create a database named `ecom`
2. Import the SQL file: `ecom.sql`
3. Update database credentials in all PHP files if needed (currently set for XAMPP/WAMP default: `root` with no password)


##. Default Login Credentials

**Admin:**
- Email: `admin@zack.com`
- Password: `password`

**Institution / Parent / Student:**
- Default Password: `password`
- Use phone number or email to login

## How to Use

1. **Admin** → `admin-login.php`
2. **Students & Parents** → `student-login.php`
3. **Schools** → Login via main `login.php` (Institution tab)

**Workflow:**
- Admin creates products and kits
- Schools request quotations
- Parents/Students add items to cart → Checkout → Pay via Bank
- Admin confirms payment and updates delivery status

## Payment & Delivery

- Currently supports **manual payment** ( Bank Transfer)
- Delivery is arranged to **school premises**
- Admin updates order status (`pending` → `paid` → `processing` → `shipped` → `delivered`)

## Future Enhancements

- Full online payment gateway (e.g., Daraja API / Pesapal)
- SMS notifications
- Order tracking with estimated delivery date
- Kit builder with product selection
- Reports & analytics dashboard
- Mobile app version

## Security Notes

- Change default admin password immediately after first login
- Never commit real database credentials
- Use strong passwords in production

---

