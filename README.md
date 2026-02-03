# E-MORS (E-Palengke Market Operations and Revenue System)

<div align="center">
  <img src="https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12.0">
  <img src="https://img.shields.io/badge/Livewire-4.0-4E56A6?style=for-the-badge&logo=livewire&logoColor=white" alt="Livewire 4.0">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS 4.0">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge" alt="License">
</div>

## 📋 Overview

**E-MORS** (E-Palengke Market Operations and Revenue System) is a comprehensive Operations Management System (OMS) designed to empower public markets with modern technology for efficient operations and transparent revenue management. The system digitizes and streamlines market operations, vendor management, revenue tracking, and administrative processes.

### Key Features

- 🏪 **Vendor Management** - Complete vendor registration, tracking, and management system
- 💰 **Revenue Tracking** - Real-time revenue monitoring and financial reporting
- 📊 **Stall Management** - Digital stall allocation and occupancy tracking
- 🔔 **Notifications** - Automated alerts for payments, renewals, and important updates
- 📱 **Mobile Responsive** - Fully responsive design for desktop, tablet, and mobile devices
- 🔐 **Two-Factor Authentication** - Enhanced security with 2FA support
- 📈 **Analytics Dashboard** - Comprehensive insights and data visualization
- 🧾 **Payment Processing** - Streamlined payment collection and receipt generation

## 🚀 Tech Stack

- **Backend Framework:** Laravel 12.0
- **Frontend:** Livewire 4.0 + Livewire Flux 2.9
- **Styling:** Tailwind CSS 4.0
- **Authentication:** Laravel Fortify
- **Database:** MySQL/PostgreSQL
- **Build Tool:** Vite 7.0
- **Testing:** Pest 4.3
- **PHP Version:** 8.2+

## 📦 Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL 8.0+ or PostgreSQL 13+
- Git

### Quick Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd E-MORS
   ```

2. **Run the setup script**
   ```bash
   composer setup
   ```
   
   This will automatically:
   - Install PHP dependencies
   - Create `.env` file from `.env.example`
   - Generate application key
   - Run database migrations
   - Install Node.js dependencies
   - Build frontend assets

3. **Configure environment**
   
   Edit your `.env` file with your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=emors
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

4. **Start the development server**
   ```bash
   # Terminal 1 - Laravel development server
   php artisan serve
   
   # Terminal 2 - Vite development server
   npm run dev
   ```

5. **Access the application**
   
   Open your browser and navigate to `http://localhost:8000`

### Manual Setup

If you prefer manual installation:

```bash
# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Install Node.js dependencies
npm install

# Build assets
npm run build
```

## 🛠️ Development

### Running Development Servers

```bash
# Laravel development server
php artisan serve

# Vite development server (hot reload)
npm run dev
```

### Database Commands

```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Refresh database (drop all tables and re-run migrations)
php artisan migrate:fresh

# Seed database with sample data
php artisan db:seed
```

### Cache Management

```bash
# Clear application cache
php artisan cache:clear

# Clear view cache
php artisan view:clear

# Clear route cache
php artisan route:clear

# Clear config cache
php artisan config:clear

# Clear all caches
php artisan optimize:clear
```

### Code Quality

```bash
# Run Laravel Pint (code formatting)
./vendor/bin/pint

# Run tests with Pest
php artisan test

# Run specific test
php artisan test --filter=TestName
```

## 📱 Features in Detail

### Authentication System
- User registration with email verification
- Secure login with password hashing
- Two-factor authentication support
- Password reset functionality
- Session management

### Vendor Management
- Vendor registration and profile management
- Stall assignment and tracking
- Vendor categorization
- Document upload and verification
- Renewal reminders

### Revenue System
- Payment tracking and history
- Receipt generation
- Revenue analytics and reports
- Payment reminders
- Financial dashboards

### Admin Dashboard
- Real-time market statistics
- Vendor analytics
- Revenue insights
- System notifications
- User management

## 🌐 Project Structure

```
E-MORS/
├── app/
│   ├── Actions/         # Fortify actions
│   ├── Http/
│   │   ├── Controllers/ # HTTP controllers
│   │   └── Livewire/    # Livewire components
│   ├── Models/          # Eloquent models
│   └── Providers/       # Service providers
├── config/              # Configuration files
├── database/
│   ├── factories/       # Model factories
│   ├── migrations/      # Database migrations
│   └── seeders/         # Database seeders
├── public/              # Public assets
├── resources/
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   └── views/          # Blade templates
│       ├── components/ # Reusable components
│       ├── layouts/    # Layout templates
│       └── pages/      # Page views
├── routes/             # Route definitions
├── storage/            # Storage files
├── tests/              # Test files
└── vendor/             # Composer dependencies
```

## 🎨 UI Components

The project uses a consistent design system with:
- **Color Palette:** Orange/Amber gradient primary, Zinc dark backgrounds
- **Typography:** System font stack with responsive sizing
- **Components:** Reusable Blade components in `/resources/views/components/`
- **Layouts:** Responsive layouts for auth and main application
- **Footer:** Centralized footer component used across all pages

## 🔒 Security Features

- CSRF protection on all forms
- SQL injection prevention via Eloquent ORM
- XSS protection with Blade templating
- Password hashing with bcrypt
- Two-factor authentication support
- Session security and timeout
- Rate limiting on authentication routes

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run tests with coverage
php artisan test --coverage

# Run specific test suite
php artisan test --testsuite=Feature
```

## 📝 Environment Variables

Key environment variables to configure:

```env
APP_NAME=E-MORS
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=emors
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025

SESSION_DRIVER=database
QUEUE_CONNECTION=sync
```

## 🚢 Deployment

### Production Build

```bash
# Install dependencies
composer install --optimize-autoloader --no-dev

# Build assets
npm run build

# Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force
```

### Environment Setup

1. Set `APP_ENV=production` and `APP_DEBUG=false`
2. Configure production database credentials
3. Set up proper mail driver
4. Configure queue workers for background jobs
5. Set up SSL certificate for HTTPS
6. Configure proper session and cache drivers

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License.

## 👨‍💻 Author

**CodeHub.Site**
- Website: [codehub.site](https://codehub.site)
- Copyright © 2026 E-MORS

## 🙏 Acknowledgments

- Built with [Laravel](https://laravel.com)
- UI powered by [Livewire](https://livewire.laravel.com) and [Livewire Flux](https://flux.laravel.com)
- Styled with [Tailwind CSS](https://tailwindcss.com)
- Icons and design inspiration from modern OMS platforms

## 📞 Support

For support, please contact the development team or open an issue in the repository.

---

<div align="center">
  <strong>Built with ❤️ using Laravel 12 and Livewire</strong>
  <br>
  <sub>E-Palengke Market Operations and Revenue System</sub>
</div>
