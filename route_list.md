# ROUTES

# 1. Application Routes

## Auth

- POST /auth/register
- POST /auth/login
- POST /auth/logout
- POST /auth/refresh
- POST /auth/forgot-password
- POST /auth/reset-password
- POST /auth/verify-email

## Profile

- GET /profile
- PUT /profile
- DELETE /profile

## Settings

- GET /settings
- PUT /settings
- DELETE /settings

## Dashboard

- GET /dashboard

## Notification

- GET /notification

## Application Background

- GET /history
- GET /history/:id
- GET /history/:id/download
- GET /history/:id/download/:type
- DELETE /history/:id

- GET /billing
- GET /billing/:id
- GET /billing/:id/download
- GET /billing/:id/download/:type

- GET /billing/invoice
- GET /billing/invoice/:id
- GET /billing/invoice/:id/download
- GET /billing/invoice/:id/download/:type

## Application Operation

- POST /operation/start
- POST /operation/pause/:id
- POST /operation/resume/:id
- POST /operation/cancel/:id
- GET /operation/status/:id
- GET /operation/history
- GET /operation/history/:id


# 2. Github Routes

- GET /github/auth
- GET /github/callback

# 3. Google Routes

- GET /google/auth
- GET /google/callback

# 4. Homepage Routes

- GET /pricing
- GET /about-us
- GET /contact
- POST /contact
- GET /terms
- GET /privacy


