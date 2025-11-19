#!/bin/bash

##############################################################################
# ichri.tn - Quick Setup Script (Production Ready)
# Description: Automated setup script for immediate deployment
# Author: Claude Code
# Version: 2.0.0
##############################################################################

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
print_header() {
    echo -e "\n${BLUE}========================================${NC}"
    echo -e "${BLUE}$1${NC}"
    echo -e "${BLUE}========================================${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

# Check if running as root (not recommended for production)
check_user() {
    if [ "$EUID" -eq 0 ]; then
        print_warning "Running as root. Consider using a non-root user for production."
        read -p "Continue anyway? (y/n) " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            exit 1
        fi
    fi
}

# Check system requirements
check_requirements() {
    print_header "Checking System Requirements"

    # Check PHP
    if ! command -v php &> /dev/null; then
        print_error "PHP is not installed. Please install PHP 8.2 or higher."
        exit 1
    fi

    PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -f1-2 -d".")
    if [ "$(printf '%s\n' "8.2" "$PHP_VERSION" | sort -V | head -n1)" != "8.2" ]; then
        print_error "PHP version $PHP_VERSION is not supported. Requires PHP 8.2+"
        exit 1
    fi
    print_success "PHP $PHP_VERSION detected"

    # Check Composer
    if ! command -v composer &> /dev/null; then
        print_error "Composer is not installed. Please install Composer first."
        exit 1
    fi
    print_success "Composer detected"

    # Check Node.js (optional for frontend)
    if command -v node &> /dev/null; then
        NODE_VERSION=$(node -v)
        print_success "Node.js $NODE_VERSION detected"
    else
        print_warning "Node.js not found (optional for frontend)"
    fi

    # Check MySQL/MariaDB
    if command -v mysql &> /dev/null; then
        print_success "MySQL/MariaDB detected"
    else
        print_warning "MySQL/MariaDB not found. Ensure database server is accessible."
    fi

    # Check Redis
    if command -v redis-cli &> /dev/null; then
        print_success "Redis CLI detected"
    else
        print_warning "Redis CLI not found. Redis recommended for production."
    fi
}

# Navigate to backend directory
cd backend

# Install PHP dependencies
install_dependencies() {
    print_header "Installing Dependencies"

    print_info "Installing Composer dependencies..."
    composer install --optimize-autoloader --no-dev
    print_success "Composer dependencies installed"
}

# Setup environment
setup_environment() {
    print_header "Setting Up Environment"

    if [ ! -f .env ]; then
        print_info "Creating .env file from .env.example..."
        cp .env.example .env
        print_success ".env file created"

        # Generate application key
        print_info "Generating application key..."
        php artisan key:generate
        print_success "Application key generated"

        # Generate JWT secret
        print_info "Generating JWT secret..."
        php artisan jwt:secret || print_warning "JWT secret generation skipped (package may need configuration)"

        print_warning "⚠️  IMPORTANT: Edit .env file with your database and service credentials!"
        echo ""
        echo "Required configurations:"
        echo "  - DB_* (Database credentials)"
        echo "  - REDIS_* (Redis connection)"
        echo "  - MAIL_* (Email service)"
        echo "  - JWT_SECRET (Already generated)"
        echo ""
        read -p "Press ENTER after you've configured .env file..."
    else
        print_success ".env file already exists"
    fi
}

# Setup database
setup_database() {
    print_header "Setting Up Database"

    print_info "Running database migrations..."
    php artisan migrate --force
    print_success "Database migrations completed"

    read -p "Do you want to seed the database with demo data? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        print_info "Seeding database..."
        php artisan db:seed --force
        print_success "Database seeded successfully"
    else
        print_info "Skipping database seeding"
    fi
}

# Setup storage
setup_storage() {
    print_header "Setting Up Storage"

    print_info "Creating storage symlink..."
    php artisan storage:link || print_warning "Storage link already exists or failed"
    print_success "Storage setup completed"

    # Set permissions
    print_info "Setting storage permissions..."
    chmod -R 775 storage bootstrap/cache
    print_success "Permissions set"
}

# Optimize application
optimize_app() {
    print_header "Optimizing Application"

    print_info "Clearing all caches..."
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    print_success "Caches cleared"

    print_info "Caching configuration..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    print_success "Configuration cached"

    print_info "Optimizing autoloader..."
    composer dump-autoload -o
    print_success "Autoloader optimized"
}

# Start services
start_services() {
    print_header "Starting Services"

    read -p "Do you want to start the development server? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        print_info "Starting Laravel development server on http://localhost:8000"
        print_warning "Press Ctrl+C to stop the server"
        echo ""
        php artisan serve
    else
        print_info "Skipping server start. Use 'php artisan serve' to start manually."
    fi
}

# Health check
health_check() {
    print_header "Running Health Check"

    print_info "Checking application health..."

    # Wait a moment if server just started
    sleep 2

    # Try to access health endpoint
    if command -v curl &> /dev/null; then
        HEALTH_RESPONSE=$(curl -s http://localhost:8000/api/v1/health || echo "failed")
        if [[ $HEALTH_RESPONSE == *"ok"* ]]; then
            print_success "Application is healthy!"
        else
            print_warning "Health check endpoint not responding. Server may not be running."
        fi
    else
        print_info "curl not found. Skipping automated health check."
        print_info "You can manually check: http://localhost:8000/api/v1/health"
    fi
}

# Display completion message
completion_message() {
    print_header "Setup Complete!"

    echo -e "${GREEN}✓ ichri.tn is now ready to use!${NC}\n"

    echo "📚 Available Endpoints:"
    echo "  - Health Check: http://localhost:8000/api/v1/health"
    echo "  - Detailed Health: http://localhost:8000/api/v1/health/detailed"
    echo "  - Metrics: http://localhost:8000/api/v1/health/metrics"
    echo "  - API Documentation: See API_DOCUMENTATION.md"
    echo ""

    echo "🚀 Next Steps:"
    echo "  1. Review .env configuration"
    echo "  2. Configure external services (Twilio, AWS S3, Firebase)"
    echo "  3. Setup queue workers: php artisan queue:work"
    echo "  4. Setup task scheduler: * * * * * php artisan schedule:run"
    echo "  5. Run tests: php artisan test"
    echo "  6. Import Postman collection for API testing"
    echo ""

    echo "📖 Documentation:"
    echo "  - README.md - Project overview"
    echo "  - INSTALLATION.md - Detailed installation guide"
    echo "  - API_DOCUMENTATION.md - Complete API reference"
    echo "  - PRODUCTION_DEPLOY.md - Production deployment guide"
    echo ""

    echo "🐛 Troubleshooting:"
    echo "  - Check logs: tail -f storage/logs/laravel.log"
    echo "  - Clear caches: php artisan cache:clear"
    echo "  - Rebuild: composer install && php artisan migrate:fresh --seed"
    echo ""

    print_success "Happy coding! 🎉"
}

# Main execution
main() {
    print_header "ichri.tn Quick Setup - Version 2.0.0"

    check_user
    check_requirements
    install_dependencies
    setup_environment
    setup_database
    setup_storage
    optimize_app
    completion_message

    read -p "Start development server now? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        start_services
    else
        print_info "Setup complete. Run 'php artisan serve' when ready."
    fi
}

# Run main function
main
