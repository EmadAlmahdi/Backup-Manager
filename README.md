# CookieManager

`CookieManager` is a comprehensive PHP utility class designed for secure and efficient management of HTTP cookies. It abstracts the complexities involved in cookie management, providing an intuitive interface for setting, retrieving, checking, and deleting cookies. With built-in support for modern web security features like HttpOnly, Secure flags, and the SameSite attribute, `CookieManager` ensures your web applications handle cookies in a secure manner.

## Features

- **Secure Cookie Setting**: Easily set cookies with extensive options, ensuring compliance with security best practices.
- **Cookie Retrieval**: Safely retrieve the value of a specified cookie, mitigating the risk of unauthorized access.
- **Cookie Existence Check**: Quickly check whether a cookie exists, allowing for conditional logic based on cookie availability.
- **Cookie Deletion**: Efficiently delete cookies, with support for advanced options like path, domain, and security attributes.
- **Modern Security Compliance**: Embraces HttpOnly, Secure flags, and SameSite attribute to enhance protection against common web vulnerabilities like CSRF and XSS.

## Installation

Install `CookieManager` using Composer to seamlessly integrate it into your PHP project:

```bash
composer require temant/cookie-manager
```

## Usage

Integrating `CookieManager` into your project is straightforward. Here are some common use cases:

### Setting a Cookie

```php
use Temant\CookieManager\CookieManager;

// Set a cookie with a 1-hour expiration
CookieManager::set('cookie_name', 'abc123', time() + 3600);
```

### Retrieving a Cookie

```php
// Get the value of a cookie
$userSession = CookieManager::get('cookie_name');
```

### Checking for a Cookie

```php
// Check if a cookie exists
$isLoggedIn = CookieManager::has('cookie_name');
```

### Deleting a Cookie

```php
// Delete a cookie
CookieManager::delete('cookie_name');
```

## Contributing

Contributions are what make the open-source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement". Don't forget to give the project a star! Thanks again!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## Running Tests

To run tests, navigate to the project directory and run the following command:

```bash
phpunit --bootstrap vendor/autoload.php tests
```

Ensure you have PHPUnit installed and configured correctly in your project.

## License

Distributed under the MIT License. See `LICENSE` for more information.