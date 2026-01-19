# BuyCryptoCardBot

BuyCryptoCardBot is a Telegram bot project built with PHP 8.3, designed to provide virtual Visa & Mastercard cards with no KYC and 0% transaction fees.

## Features

- **Telegram Bot Interface**: Interactive menus for buying cards, depositing USDT, and checking fees.
- **Dual-Purpose Landing Page**: `index.php` serves as both the bot's webhook handler and a basic web landing page.
- **USDT Payments**: Placeholder implementation for USDT (TRC20) deposits.
- **Easy Setup**: Includes a utility script to set up the Telegram webhook.

## Project Structure

- `index.php`: The main entry point. Handles Telegram updates and displays the landing page.
- `config.php`: Configuration file that retrieves settings like `BOT_TOKEN` from environment variables.
- `set_webhook.php`: A helper script to register your bot's URL with the Telegram API.

## Setup and Deployment

This project is designed to be deployed on [Wasmer](https://wasmer.io/) and is currently hosted at [https://buycryptocardbot.wasmer.app/](https://buycryptocardbot.wasmer.app/).

### Prerequisites

- PHP 8.3
- A Telegram Bot Token from [@BotFather](https://t.me/BotFather)

### Configuration

Set the following environment variable in your deployment environment:

- `BOT_TOKEN`: Your Telegram Bot API token.

### Setting the Webhook

To start receiving updates from Telegram, you must set the webhook by running:

```bash
php set_webhook.php
```

Or by visiting `https://buycryptocardbot.wasmer.app/set_webhook.php` in your browser (if public access is allowed).

## License

MIT
