#!/bin/bash

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Print functions
print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_header() {
    echo -e "${BLUE}
╔══════════════════════════════════════════╗
║   FFO Backend Production Deployment      ║
╚══════════════════════════════════════════╝${NC}
"
}

# Check if docker and docker-compose are installed
check_requirements() {
    print_info "Checking requirements..."
    
    if ! command -v docker &> /dev/null; then
        print_error "Docker is not installed!"
        exit 1
    fi
    
    if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
        print_error "Docker Compose is not installed!"
        exit 1
    fi
    
    print_success "Requirements met"
}

# Setup environment
setup_environment() {
    print_info "Setting up environment..."
    
    if [ ! -f ".env" ]; then
        if [ -f ".env.production" ]; then
            print_warning ".env not found, copying from .env.production"
            cp .env.production .env
            print_warning "Please edit .env file and update sensitive values!"
            read -p "Press enter to continue after editing .env..."
        else
            print_error ".env.production not found!"
            exit 1
        fi
    fi
    
    # Check critical env variables
    if ! grep -q "APP_KEY=base64:" .env; then
        print_warning "APP_KEY not set, will be generated on first run"
    fi
    
    if grep -q "CHANGE_THIS" .env; then
        print_error "Found CHANGE_THIS placeholders in .env file!"
        print_error "Please update all sensitive values in .env"
        exit 1
    fi
    
    print_success "Environment configured"
}

# Create required directories
setup_directories() {
    print_info "Creating required directories..."
    
    mkdir -p storage/app/public
    mkdir -p storage/framework/{cache,sessions,testing,views}
    mkdir -p storage/logs
    mkdir -p bootstrap/cache
    mkdir -p docker/nginx/ssl
    mkdir -p docker/postgres/init
    
    print_success "Directories created"
}

# Build and start containers
deploy_containers() {
    print_info "Building Docker images..."
    docker-compose build --no-cache
    
    print_success "Images built successfully"
    
    print_info "Starting containers..."
    docker-compose up -d
    
    print_success "Containers started"
}

# Wait for services
wait_for_services() {
    print_info "Waiting for services to be healthy..."
    
    local max_wait=60
    local wait_time=0
    
    while [ $wait_time -lt $max_wait ]; do
        if docker-compose ps | grep -q "healthy"; then
            print_success "Services are healthy"
            return 0
        fi
        echo -n "."
        sleep 2
        wait_time=$((wait_time + 2))
    done
    
    print_warning "Services health check timed out, but continuing..."
}

# Show logs
show_logs() {
    print_info "Showing application logs..."
    docker-compose logs --tail=50 app
}

# Display deployment info
display_info() {
    local APP_URL=$(grep APP_URL .env | cut -d '=' -f2)
    
    echo -e "${GREEN}
╔══════════════════════════════════════════╗
║      Deployment Successful! 🎉           ║
╚══════════════════════════════════════════╝${NC}

${BLUE}📊 Container Status:${NC}
"
    docker-compose ps
    
    echo -e "
${BLUE}🌐 Access Information:${NC}
   API URL: ${APP_URL}
   Health Check: ${APP_URL}/health

${BLUE}📝 Useful Commands:${NC}
   View logs:        docker-compose logs -f
   Restart:          docker-compose restart
   Stop:             docker-compose down
   Database shell:   docker-compose exec db psql -U \$DB_USERNAME -d \$DB_DATABASE
   App shell:        docker-compose exec app bash
   Run migrations:   docker-compose exec app php artisan migrate
   Clear cache:      docker-compose exec app php artisan cache:clear

${YELLOW}⚠️  Important Notes:${NC}
   1. Make sure your firewall allows ports 80 and 443
   2. Configure your domain DNS to point to this server
   3. For SSL, place your certificates in docker/nginx/ssl/
   4. Backup your .env file and database regularly
   5. Monitor logs: docker-compose logs -f app

${GREEN}✨ Deployment completed successfully!${NC}
"
}

# Rollback function
rollback() {
    print_error "Deployment failed! Rolling back..."
    docker-compose down
    print_info "Rollback completed"
    exit 1
}

# Main deployment
main() {
    print_header
    
    # Set trap for errors
    trap rollback ERR
    
    check_requirements
    setup_environment
    setup_directories
    deploy_containers
    wait_for_services
    show_logs
    display_info
}

# Run main function
main
