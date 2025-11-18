#!/bin/bash

# ichri.tn Deployment Script
# This script helps deploy the ichri.tn application to different environments

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

print_header() {
    echo -e "\n${GREEN}========================================${NC}"
    echo -e "${GREEN}  ichri.tn Deployment Script${NC}"
    echo -e "${GREEN}========================================${NC}\n"
}

# Check if docker is installed
check_docker() {
    if ! command -v docker &> /dev/null; then
        print_error "Docker is not installed. Please install Docker first."
        exit 1
    fi
    print_success "Docker is installed"
}

# Check if docker-compose is installed
check_docker_compose() {
    if ! command -v docker-compose &> /dev/null; then
        print_error "Docker Compose is not installed. Please install Docker Compose first."
        exit 1
    fi
    print_success "Docker Compose is installed"
}

# Deploy function
deploy() {
    local env=$1

    print_header
    print_info "Deploying to environment: $env"

    # Check prerequisites
    check_docker
    check_docker_compose

    # Stop existing containers
    print_info "Stopping existing containers..."
    docker-compose down

    # Build and start containers
    print_info "Building and starting containers..."
    docker-compose up -d --build

    # Wait for services to be ready
    print_info "Waiting for services to be ready..."
    sleep 10

    # Check if backend is running
    if docker-compose ps | grep -q "backend.*Up"; then
        print_success "Backend is running"
    else
        print_error "Backend failed to start"
        exit 1
    fi

    # Check if web is running
    if docker-compose ps | grep -q "web.*Up"; then
        print_success "Web dashboard is running"
    else
        print_error "Web dashboard failed to start"
        exit 1
    fi

    # Check if MySQL is running
    if docker-compose ps | grep -q "mysql.*Up"; then
        print_success "MySQL is running"
    else
        print_error "MySQL failed to start"
        exit 1
    fi

    print_success "\n🎉 Deployment successful!"
    print_info "\nServices:"
    print_info "  - API Backend: http://localhost:8000"
    print_info "  - Web Dashboard: http://localhost:3000"
    print_info "  - MySQL: localhost:3306"
    print_info "  - Redis: localhost:6379"
    print_info "  - ElasticSearch: http://localhost:9200"

    print_info "\nTo view logs:"
    print_info "  docker-compose logs -f"

    print_info "\nTo stop all services:"
    print_info "  docker-compose down"
}

# Rollback function
rollback() {
    print_info "Rolling back deployment..."
    docker-compose down
    print_success "Rollback completed"
}

# Show help
show_help() {
    echo "Usage: ./deploy.sh [COMMAND]"
    echo ""
    echo "Commands:"
    echo "  production    Deploy to production environment"
    echo "  staging       Deploy to staging environment"
    echo "  development   Deploy to development environment (default)"
    echo "  rollback      Rollback the deployment"
    echo "  help          Show this help message"
    echo ""
    echo "Examples:"
    echo "  ./deploy.sh production"
    echo "  ./deploy.sh staging"
    echo "  ./deploy.sh rollback"
}

# Main script
case "$1" in
    production)
        deploy "production"
        ;;
    staging)
        deploy "staging"
        ;;
    development|"")
        deploy "development"
        ;;
    rollback)
        rollback
        ;;
    help|--help|-h)
        show_help
        ;;
    *)
        print_error "Unknown command: $1"
        show_help
        exit 1
        ;;
esac
