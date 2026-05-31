# MyApp

A custom PHP MVC application built from scratch without frameworks.

## Features

- Authentication
- Registration/Login
- CSRF Protection
- Argon2id Password Hashing
- Session Management
- Middleware
- Dependency Injection Container
- Request Validation
- Docker Support
- PHPUnit Test Suite

## Architecture

Controller
↓
Request Validation
↓
Service Layer
↓
Model Layer
↓
Database

## Tech Stack

- PHP 8.x
- MySQL
- PHPUnit
- Docker
- Nginx

## Installation

git clone ...
cd myApp

docker compose up -d

## Running Tests

vendor/bin/phpunit

## Project Structure

App/
├── Controllers/
├── Services/
├── Models/
├── Requests/
├── Middleware/
├── Security/
├── Core/

## Security

- CSRF tokens
- Argon2id password hashing
- Session protection
- Login throttling

## Test Coverage

- Controllers
- Services
- Models
- Middleware
- Requests
- Security

142+ tests

## Future Improvements

- Route parameters
- Repository pattern
- API layer
- Role-based permissions