# Eastern Sweets

A branded PHP and MySQL commerce platform for Eastern Sweets, Bakers & Nimco. The project includes a responsive customer storefront, session cart, checkout and order tracking, customer accounts, a protected administration panel, content management, reporting, and payment gateway integration.

## Highlights

- Responsive storefront with animated hero and category banners
- Product catalogue, variants, filters, search, cart drawer, coupons, and checkout
- Customer authentication, profile, addresses, order history, and tracking
- Separate admin authentication with product, category, order, customer, payment, coupon, banner, and content management
- Database-driven logo, pattern, theme colours, homepage copy, testimonials, footer, and static pages
- Validated JPG, PNG, and WebP uploads with live previews and organised upload folders
- PDO prepared statements, CSRF protection, escaped output, password hashing, login throttling, and isolated admin sessions
- Cash on Delivery and Safepay hosted checkout structure
- Docker and Railway deployment configuration

## Stack

PHP 8.2, MySQL/MariaDB, PDO, HTML5, custom CSS, and vanilla JavaScript. The application uses an MVC-style structure under `controllers/`, `models/`, `views/`, and `includes/`.

## Local Setup

1. Place the project at `C:\xammp2\htdocs\easternsweets` or the equivalent XAMPP/WAMP/Laragon web root.
2. Start Apache and MySQL.
3. Import `database/eastern_sweets.sql` through phpMyAdmin.
4. Run `php scripts/seed_category_expansion.php` once to apply the category and storefront content migration.
5. Review database credentials in `includes/db.php` or provide the environment variables documented in `.env.example`.
6. Open `http://localhost/easternsweets/`.

Admin login: `http://localhost/easternsweets/admin/login`

- Email: `admin@easternsweets.com`
- Password: `admin123`

Change the seeded administrator password before using the application publicly.

## Deployment

The application can run on standard PHP/MySQL shared hosting or a Docker-compatible host. For shared hosting, upload the project into the web root, import `database/eastern_sweets.sql`, configure database credentials through environment variables or `includes/db.php`, and keep the `uploads/` directory writable and persistent.

For container hosting, the included `Dockerfile` and `deploy/start.sh` initialise an empty database and apply recorded data migrations. Mount persistent storage at `/var/www/html/uploads` so admin uploads survive container replacements.

## Payments

Cash on Delivery works without external credentials. Safepay uses server-side sandbox or production credentials from environment variables; secrets must never be committed. See `.env.example` and `includes/payment-config.php` for the required keys and mode settings.

## Photo Credits

Storefront category photography uses free-to-use images from Pexels: [ice cream](https://www.pexels.com/photo/scoops-of-different-ice-cream-on-plate-1683546/), [samosa](https://www.pexels.com/photo/crunchy-samosa-over-a-ceramic-plate-9027521/), [pastries](https://www.pexels.com/photo/assortment-of-delicious-bakery-pastries-29513762/), [falooda](https://www.pexels.com/photo/delicious-sweet-dessert-served-in-glass-on-table-2835351/), [dairy](https://www.pexels.com/photo/rustic-still-life-with-dairy-and-bread-37377279/), [gift boxes](https://www.pexels.com/photo/stacked-gift-boxes-with-different-colors-264985/), [snacks](https://www.pexels.com/photo/potato-chips-in-a-bowl-7886048/), [dry fruits](https://www.pexels.com/photo/assorted-dried-fruits-and-nuts-in-bowls-32282191/), and [bakery items](https://www.pexels.com/photo/assorted-baked-goods-7416123/).

Eastern Sweets branding and product photography remain the property of their respective owner.
