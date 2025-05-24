=== Contact Form 7 Stripe Integration Addon ===
Contributors: payaddons
Tags: contact form 7, form, stripe, credit card, google pay
Requires at least: 4.9
Tested up to: 6.8
Requires PHP: 5.6
Stable tag: 1.5.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Build your one-time and recurring payment form together with contact form 7 and Stripe without creating an entire online store.

== Description ==

Contact Form 7 Stripe Payment addon is a payment solution to build your payment page or form with contact form 7. It’s the easiest way to create any one-time or recurring order form, payment form, or subscription event with all kinds payment methods like Credit Cards, PayPal, Google Pay, ApplePay, Afterpay, Affirm, ACH, Klarna, iDEAL, and more.

With stripe payment addons for Contact Form 7, you can create custom payment forms and accept payments directly on your website using Stripe. Whether you're selling products, services, or accepting donations, Payment Addons for Contact Form 7 makes it easy to get paid.

== Features ==

🔒 Form Checkout Redirect

* Seamless redirection to Stripe-hosted checkout pages for one-time payments after submitting your form.
* Dynamic form field for the pricing setting.
* Sending CF7 email after payment success. (webhook is required to setup)
* PCI-compliant payment processing.
* Full integration with Stripe Link for faster repeat purchases. (Pro)

<a href="https://cf7-docs.payaddons.com/basics/contact-form-7-checkout-redirect">documentation</a>
[online demo](https://payaddons.com/contact-form-7-stripe-redirect-demo/)

🔁 Flexible Subscriptions (Pro)

* Create recurring payment plans with custom intervals
* Automated billing and payment handling

[documentation](https://cf7-docs.payaddons.com/basics/contact-form-7-checkout-redirect#subscription-payment-settings-pro)
[online demo](https://payaddons.com/contact-form-7-stripe-redirect-demo/)

💳 Embedded Credit Card Forms (Pro)

* Integrate credit card fields directly into your forms
* Support for dynamic pricing options
* Custom subscription configurations
* Real-time card validation

<a href="https://cf7-docs.payaddons.com/basics/contact-form-7-credit-card">documentation</a>
[online demo](https://payaddons.com/contact-form-7-stripe-credit-card-demo/)

💳 Embedded Payment Elements (Pro)

* Multiple payment methods with one-time & recurring support, including Credit Cards, Google Pay, ApplePay, WeChat Pay, Alipay, Afterpay, ACH, Klarna, iDEAL, FPX, Grabpay, OXXO, Multibanco, Bancontact, EPS, P24, Giropay, Affirm, and more.
* Mutiple layouts, dark and light themes, and so on.
* Dynamic to enable Google Pay & Apple Pay.

[documentation](https://cf7-docs.payaddons.com/basics/contact-form-7-payment-element)
[online demo](https://payaddons.com/contact-form-7-stripe-payment-element-demo/)

📧 Email Notification (Pro)

We currently offer notifications for three event types, You can design your email templates with built-in placeholder fields like amount, currency, customer email, address, etc...

* Payment Succeeded
* Payment Failed
* Customer Invoice

== Frequently Asked Questions ==

= Does this plugin only support stripe gateway? What about PayPal? =
Yes, but Only for the Stripe Account In Europe.

= Does this support recurring payments, like for subscriptions? =
Yes, you can feel free to provide any subscription.

= Does this require an SSL certificate? =

Yes! In Live Mode, an SSL certificate must be installed on your site to use Stripe.

= Does this support both production mode and sandbox mode for testing? =

Yes, it does - You can switch them in plugin setting page.

= Can I disable WP REST API? =
No, this plugin is using WP REST API. So please make sure it is opening or just add stripe-express API into the whitelist by some plugins like [disable-json-api](https://wordpress.org/plugins/disable-json-api/).

== Screenshots ==
1. setting 
2. stripe checkout redirection
3. stripe credit card 
4. stripe payment element 

== Changelog ==

= 1.5.0 =
feat: add redirection receipt shortcode.
feat: add save metadata option.
feat: add mix type support for form field.
feat: add payment_id mail tag.

= 1.4.0 =
feat: add email notifications.

= 1.3.0 =
feat: add payment element field for multiple payment methods.

= 1.2.0 =
feat: add credit card field.

= 1.1.0 =
feat: add stripe link for checkout redirection.

= 1.0.0 =
First Release

== Upgrade Notice ==
None.
