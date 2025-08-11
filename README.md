# Llymar - Frameless Glazing Systems Management Platform

A comprehensive business management platform for Llymar, a Russian manufacturer of frameless glazing systems for residential and commercial properties.

## 🏢 About Llymar

Llymar is a reliable Russian manufacturer of frameless glazing systems for residential and commercial properties. We create modern solutions that make spaces brighter, more comfortable, and visually more spacious. We implement unique glazing projects for balconies, terraces, loggias, facades, and partitions, ensuring high quality at every stage from design to installation.

## 🚀 Features

### 📊 Business Management
- **Order Management**: Complete order lifecycle management with status tracking
- **Customer Management**: User accounts with role-based permissions
- **Inventory Management**: Item catalog with categories, pricing, and warehouse tracking
- **Commission System**: Hierarchical commission tracking for dealers and agents
- **Contract Generation**: Automated contract and document generation

### 🧮 Calculator & Pricing
- **Interactive Calculator**: Advanced pricing calculator for glazing projects
- **Dynamic Pricing**: Multi-tier pricing system with factors (KZ, K1-K4)
- **Cart System**: Shopping cart functionality with custom pricing
- **Commercial Offers**: Generate professional commercial offer PDFs

### 📐 Technical Tools
- **DXF Integration**: CAD file generation and processing
- **Sketcher**: Interactive sketching tool for project visualization
- **PDF Generation**: Automated document generation (orders, sketches, commercial offers)
- **Technical Drawings**: Generate technical documentation

### 🔐 Access Control
- **Role-Based Permissions**: 6 distinct user roles with granular permissions
  - **Super-Admin**: Full system access
  - **Operator**: Catalog and order management
  - **Manager**: Order oversight and user management
  - **Agent**: Limited order and user access
  - **Dealer**: Calculator and order history access
  - **Workman**: Order viewing and status updates

### 🏦 Financial Integration
- **Tochka Bank Integration**: Automated billing and payment processing
- **Commission Tracking**: Automated commission calculations and credits
- **Multi-currency Support**: Pricing in multiple currencies

## 🛠 Tech Stack

### Backend
- **PHP 8.2+** - Modern PHP with type declarations
- **Laravel 11** - Latest Laravel framework
- **Filament 3.2** - Modern admin panel
- **MySQL/PostgreSQL** - Database management
- **Spatie Laravel Permission** - Role and permission management

### Frontend
- **Vue.js 3** - Progressive JavaScript framework
- **Inertia.js** - Modern monolith architecture
- **TypeScript** - Type-safe JavaScript
- **Tailwind CSS** - Utility-first CSS framework
- **Pinia** - State management for Vue

### Integrations & Tools
- **DXFighter** - DXF file processing
- **DOMPDF** - PDF generation
- **PHPWord** - Document generation
- **Laravel Socialite** - OAuth authentication (Google, VK, Yandex)
- **Tochka Bank API** - Payment processing

## 📋 Requirements

- PHP 8.2 or higher
- Composer
- Node.js 18+ and NPM
- MySQL 8.0+ or PostgreSQL 13+
- Web server (Apache/Nginx)

## 🚀 Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd llymar
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database**
   Update your `.env` file with database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=llymar
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Configure Tochka Bank (Optional)**
   ```env
   TOCHKA_BASE_URL=https://api.tochka.com
   TOCHKA_CLIENT_SECRET=your_client_secret
   TOCHKA_CUSTOMER_CODE=your_customer_code
   TOCHKA_ACCOUNT_ID=your_account_id
   ```

7. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

8. **Create storage link**
   ```bash
   php artisan storage:link
   ```

9. **Build frontend assets**
   ```bash
   npm run build
   ```

## 🏃‍♂️ Development

### Start development servers
```bash
# Start all development services (Laravel, Queue, Vite)
composer run dev

# Or start individually:
php artisan serve          # Laravel server
php artisan queue:work     # Queue worker
npm run dev               # Vite development server
```

### Useful commands
```bash
# Sync permissions
php artisan command-permissions:sync

# Clear caches
php artisan optimize:clear

# Run tests
php artisan test
```

## 🏗 Project Structure

```
app/
├── Console/Commands/     # Artisan commands
├── Filament/            # Admin panel resources
├── Http/Controllers/    # HTTP controllers
├── Models/             # Eloquent models
├── Policies/           # Authorization policies
├── Services/           # Business logic services
└── Observers/          # Model observers

resources/
├── js/
│   ├── Components/     # Vue.js components
│   ├── Pages/         # Inertia.js pages
│   ├── Stores/        # Pinia stores
│   └── Utils/         # Utility functions
├── css/               # Stylesheets
└── views/             # Blade templates

database/
├── migrations/        # Database migrations
└── seeders/          # Database seeders
```

## 🔑 Default Users

After running seeders, you can access the system with:

- **Super Admin**: User ID 1 (check your seeded data)
- **Admin Panel**: `/admin`
- **User Application**: `/app`

## 📱 Main Application Routes

- `/` - Landing page
- `/auth` - Authentication page
- `/app` - User dashboard
- `/app/calculator` - Pricing calculator
- `/app/cart` - Shopping cart
- `/app/history` - Order history
- `/app/sketcher/{order_id}` - Project sketcher
- `/admin` - Admin panel (Filament)

## 🧪 Testing

```bash
# Run PHP tests
php artisan test

# Run specific test file
php artisan test tests/Feature/OrderTest.php
```

## 📄 License

This project is proprietary software owned by Llymar.

## 🤝 Contributing

This is a private business application. Contact the development team for contribution guidelines.

## 📞 Support

For technical support or business inquiries:
- Phone: +7 (989) 804 12-34
- Website: https://llymar.ru

---

*Developed for Llymar - Making spaces brighter and more comfortable through innovative frameless glazing solutions.* 