#!/bin/bash

#===============================================================================
# ichri.tn - Installation Script
# Automatic setup for development environment
#===============================================================================

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Functions
print_header() {
    echo -e "\n${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${GREEN}   ichri.tn - Installation Script${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

print_step() {
    echo -e "\n${BLUE}▶${NC} $1"
}

# Check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Main installation
print_header

print_info "Checking prerequisites..."

# Check Docker
if command_exists docker; then
    print_success "Docker is installed"
else
    print_error "Docker is not installed. Please install Docker first."
    print_info "Visit: https://docs.docker.com/get-docker/"
    exit 1
fi

# Check Docker Compose
if command_exists docker-compose; then
    print_success "Docker Compose is installed"
else
    print_error "Docker Compose is not installed. Please install Docker Compose first."
    print_info "Visit: https://docs.docker.com/compose/install/"
    exit 1
fi

# Backend Setup
print_step "Setting up Backend (Laravel 11)..."

cd backend

if [ ! -f ".env" ]; then
    print_info "Creating .env file from .env.example..."
    cp .env.example .env
    print_success ".env file created"
else
    print_warning ".env file already exists"
fi

print_info "Installing Composer dependencies..."
if command_exists composer; then
    composer install --no-interaction --prefer-dist
    print_success "Composer dependencies installed"
else
    print_warning "Composer not found locally. Will install in Docker container."
fi

cd ..

# Start Docker services
print_step "Starting Docker services..."
docker-compose up -d

print_info "Waiting for services to be ready (30 seconds)..."
sleep 30

# Setup backend in Docker
print_step "Setting up Laravel application..."

print_info "Installing Composer dependencies in Docker..."
docker-compose exec -T backend composer install --no-interaction --prefer-dist

print_info "Generating application key..."
docker-compose exec -T backend php artisan key:generate

print_info "Generating JWT secret..."
docker-compose exec -T backend php artisan jwt:secret --force

print_info "Running database migrations..."
docker-compose exec -T backend php artisan migrate --force

print_info "Seeding database with demo data..."
docker-compose exec -T backend php artisan db:seed --force

print_info "Creating storage link..."
docker-compose exec -T backend php artisan storage:link

print_info "Optimizing application..."
docker-compose exec -T backend php artisan optimize

print_success "Backend setup complete!"

# Mobile Setup
print_step "Setting up Mobile App (React Native)..."

cd mobile

if [ ! -f ".env" ]; then
    print_info "Creating mobile .env file..."
    cat > .env <<'MOBILEENV'
API_URL=http://localhost:8000/api/v1
API_TIMEOUT=30000
ONESIGNAL_APP_ID=
GOOGLE_MAPS_API_KEY=
ENVIRONMENT=development
MOBILEENV
    print_success "Mobile .env created"
fi

if command_exists npm; then
    print_info "Installing npm dependencies..."
    npm install
    print_success "Mobile dependencies installed"
else
    print_warning "npm not found. Skip mobile setup."
fi

cd ..

# Web Setup
print_step "Setting up Web Dashboard (Next.js)..."

cd web

if [ ! -f ".env.local" ]; then
    print_info "Creating web .env.local file..."
    cat > .env.local <<'WEBENV'
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
NEXT_PUBLIC_APP_NAME=ichri.tn
NEXT_PUBLIC_APP_URL=http://localhost:3000
WEBENV
    print_success "Web .env.local created"
fi

if command_exists npm; then
    print_info "Installing npm dependencies..."
    npm install
    print_success "Web dependencies installed"
else
    print_warning "npm not found. Skip web setup."
fi

cd ..

# Final checks
print_step "Running health checks..."

sleep 5

# Check backend
if curl -f http://localhost:8000/api/v1/health >/dev/null 2>&1; then
    print_success "Backend API is responding"
else
    print_warning "Backend API not responding yet. Give it a few more seconds."
fi

# Print success message
echo ""
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}   ✓ Installation Complete!${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

print_info "Services are running:"
echo -e "  ${BLUE}→${NC} Backend API: ${GREEN}http://localhost:8000${NC}"
echo -e "  ${BLUE}→${NC} Web Dashboard: ${GREEN}http://localhost:3000${NC}"
echo -e "  ${BLUE}→${NC} MySQL: ${GREEN}localhost:3306${NC}"
echo -e "  ${BLUE}→${NC} Redis: ${GREEN}localhost:6379${NC}"
echo -e "  ${BLUE}→${NC} ElasticSearch: ${GREEN}http://localhost:9200${NC}"

echo ""
print_info "Test accounts:"
echo -e "  ${BLUE}→${NC} Grocer: ${GREEN}+21698123456${NC} / ${GREEN}Password123${NC}"
echo -e "  ${BLUE}→${NC} Admin: ${GREEN}admin@ichri.tn${NC} / ${GREEN}admin123${NC}"

echo ""
print_info "Useful commands:"
echo -e "  ${BLUE}→${NC} View logs: ${YELLOW}docker-compose logs -f${NC}"
echo -e "  ${BLUE}→${NC} Run tests: ${YELLOW}docker-compose exec backend php artisan test${NC}"
echo -e "  ${BLUE}→${NC} Stop services: ${YELLOW}docker-compose down${NC}"
echo -e "  ${BLUE}→${NC} Start mobile: ${YELLOW}cd mobile && npm start${NC}"
echo -e "  ${BLUE}→${NC} Start web: ${YELLOW}cd web && npm run dev${NC}"

echo ""
print_success "🇹🇳 ichri.tn is ready! Happy coding! 🚀"
echo ""
