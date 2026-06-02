# Klanos

A custom PHP MVC application built from scratch to practice backend engineering concepts including dependency injection, authentication, validation, middleware, security, testing, and Dockerized deployment.

## Features

### Authentication & Security

* User registration and login
* Password hashing with Argon2id
* Session management
* CSRF protection
* Login attempt tracking
* Route protection through middleware

### Need List Management

* Create need list items
* View need list items
* Delete need list items
* Input validation using Form Requests

### Events System

* Event registration
* Event participation management
* Service-layer business logic

### Architecture

* Custom MVC structure
* Dependency Injection Container
* Automatic dependency resolution
* Middleware pipeline
* Service layer pattern
* Request validation layer
* Centralized error handling

### Testing

* Unit tests for:

  * Controllers
  * Services
  * Models
  * Middleware
  * Requests
  * Security components
  * Core framework classes

### Docker Support

* PHP-FPM
* Nginx
* MySQL
* Custom Docker configuration

---

## Project Structure

```text
App/
├── Controllers/
├── Core/
├── Middleware/
├── Models/
├── Requests/
├── Security/
├── Services/
└── Views/

Config/
Docker/
Public/
Routes/
Storage/
Test/
```

---

## Technologies Used

* PHP 8+
* MySQL
* PHPUnit
* Docker
* Nginx
* HTML
* CSS

---

## Core Components

### Dependency Injection Container

Custom container supporting:

* Service registration
* Singleton instances
* Automatic constructor dependency resolution

### Router

Handles:

* GET routes
* POST routes
* Middleware execution
* Controller dispatching

### Form Requests

Dedicated validation classes:

* LoginRequest
* SignupRequest
* CreateNeedRequest
* DeleteNeedRequest
* EventsRegister

### Middleware

* AuthMiddleware
* GuestMiddleware
* CsrfMiddleware

---

## Running the Project

### Clone Repository

```bash
git clone <repository-url>
cd Klanos
```

### Start Docker Environment

```bash
docker compose up -d --build
```

### Access Application

```text
http://localhost
```

---

## Running Tests

```bash
vendor/bin/phpunit
```

Run a specific test suite:

```bash
vendor/bin/phpunit Test/Services
```

---

## Learning Objectives

This project was built to gain hands-on experience with:

* MVC architecture
* Dependency Injection
* Authentication systems
* Middleware design
* Request validation
* Session security
* Unit testing
* Docker deployment
* Backend application structure

---

## Future Improvements

* Route parameters (`/users/{id}`)
* Named routes
* Repository pattern
* Response abstraction layer
* API endpoints
* Authorization policies
* Improved test coverage

---

## 🚀 Live Demo

You can view the live deployment of this application here:
[klanos.infinityfree.me](https://klanos.infinityfree.me/)

---

## Author

Marwan

Backend-focused developer interested in PHP, MySQL, system design, and building reliable web applications.
