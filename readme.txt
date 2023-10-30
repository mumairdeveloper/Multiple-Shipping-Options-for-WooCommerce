=== Multiple Shipping Options for WooCommerce ===
Contributors: Mini Logics
Tags: mini logics, minilogics, multiple shipping options for woocommerce, shipping calculator, print label, cheapest shipping rates, fedex, fedex shipping, fedex shipping rates, fedex shipping cost, fedex label, fedex tracking, fedex freight, fedex freight tracking, ups, ups shipping, ups shipping rates, ups shipping cost, ups label, ups tracking, ups freight, ups freight tracking, freight shipping, freight quote, freight calculator, woocommerce shipping, ltl freight quote fedex. multi carrier shipping plugin for woocommerce, woocommerce shipping rates, fedex plugin woocommerce, ups plugin woocommerce
Requires at least: 6.0
Tested up to: 6.2.2
Stable tag: 1.0.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

This is exclusively designed to work with the Mini Logics plugin for FedEx and UPS.
 
== Description ==

The plugin offers affordable shipping rates from multiple carriers, generates printable shipping labels, tracks shipments using various shipping APIs, and conveniently displays the results in the WooCommerce shopping cart and order pages.

== Quoting Method ==

* API testing mode
* Lowest shipping option across all carriers
* Lowest shipping option from each carrier
* Minimum shipment weight requirement for LTL Freight Shipping; Small Package Shipping will be returned otherwise
* Offer free shipping when an order's parcel shipment exceeds a certain threshold based on weight limit and cart total limit
* What to do when a product does not provide a shipping rate on the cart/checkout Page
* If the products in an order are from multiple origins and one of them does not have a return rate, the total shipping cost should not be displayed on the cart page.
* If no shipping rates are available, you have two options to consider: displaying an error message or setting a custom shipping rate.

== Origin Address ==

* Your default origin address is the store address that will be used to get shipping rates. If you want to change the origin address for individual products, go to the product page where you can make individual selections.

== Small Package Shipping ==

This feature dynamically retrieves your rates from the Small Package Shipping API and displays them in the WooCommerce shopping cart.

**UPS**
UPS Small Business offers finance solutions, expert advice, and discounts on shipping and services to help your company save time and money.

**1. Enable / Disable**

* There is an option through which we can enable or disable the API connection.

**2. API Connection**

* Account Number
* Username
* Password
* Access Key
* Showing the API connection status

**3. Domestic Services**
**Every available service option below allows you to change the service name and add markup individually.**

* UPS Ground
* UPS 2nd Day Air
* UPS 2nd Day Air A.M
* UPS Next Day Air Saver
* UPS Next Day Air
* UPS Next Day Air Early
* UPS 3 Day Select

**4. International Services**
**Every available service option below allows you to change the service name and add markup individually.**

* UPS Standard
* UPS Expedited
* UPS Express Saver
* UPS Express
* UPS Express Plus

**5. Accessorials**

* Residential delivery

**Fedex**

The Small Business Center provides shipping solutions, tools, and insights from entrepreneurs and experts.

**1. Enable / Disable**

* There is an option through which we can enable or disable the API connection

**2. API Connection**

* Key
* Password
* Account Number
* Meter Number
* Showing the API connection status

**3. Domestic Services**
**Every available service option below allows you to change the service name and add markup individually.**

* Fedex Home Delivery
* Fedex Ground
* Fedex Express Saver
* Fedex 2Day
* Fedex 2Day AM
* Fedex Standard Overnight
* Fedex Priority Overnight
* Fedex First Overnight

**4. International Services**
**Every available service option below allows you to change the service name and add markup individually.**

* Fedex International Ground
* Fedex International Economy
* Fedex International Economy Distribution
* Fedex International Economy Freight
* FedEx International First
* Fedex International Priority
* Fedex International Priority Distribution
* Fedex International Priority Freight
* Fedex International Distribution Freight

**5. Accessorials**

* Residential delivery


== LTL Freight Shipping ==

This feature dynamically retrieves your rates from the LTL Freight Shipping API and displays them in the WooCommerce shopping cart.

**UPS**

UPS Freight's Less-than-Truckload (LTL) transportation services are offered by TFI International Inc., its affiliates, or divisions (including TForce Freight), which are not affiliated with United Parcel Service, Inc. or any of its affiliates, subsidiaries, or related entities. UPS assumes no liability in connection with UPS Freight LTL transportation services or any other services offered or provided by TFI International Inc. or its affiliates, divisions, subsidiaries, or related entities. The shipping options for UPS Freight LTL transportation include value-added services, collect on delivery (C.O.D.), and various billing options. Explore all of the value-added services available to you.

**1. Enable / Disable**

* There is an option through which we can enable or disable the API connection

**2. API Connection**

* Account Number
* Username
* Password
* Access Key
* Showing the API connection status

**3. Accessorials**

* Residential delivery
* Liftgate delivery

**4. Markup**

* A markup will be added to the final rates on the cart/checkout page

**Fedex**

The FedEx Freight box is an easy way to send shipments weighing under 1,200 lbs. You don't need to classify your shipments, and you get predictable pricing, easy packing, and added security.

**1. Enable / Disable**

* There is an option through which we can enable or disable the API connection

**2. API Connection**

* Key
* Password
* Account Number
* Meter Number
* Third Party Account Number

**3. Billing Details**

* Billing Account Number
* Address
* City
* State
* Zip
* Country

**3. Physical Details**

* Address
* City
* State
* Zip
* Country
* Showing the API connection status

**4. Accessorials**

* Residential delivery
* Liftgate delivery

**5. Markup**

* A markup will be added to the final rates on the cart/checkout page

== Pallets & Boxes ==

There is a pallet solution available for LTL Freight Shipping products.

**1. Pallet properties**

* Pallet Name
* Length (in)
* Width (in)
* Max Height (in)
* Pallet Height (in)
* Pallet Weight (lbs)
* Max Weight (lbs)

There is a box solution available for Small Package Shipping products.

**2. Box properties**

* Box name
* Inner Length
* Inner Width
* Inner Height
* Outer Length
* Outer Width
* Outer Height
* Box Weight
* Max Weight

== Product Settings ==

**1. Multiple Shipping Options for WooCommerce**

* You can add, edit, update, or delete the origin by clicking on the 'Edit Origins' button
* You can assign the location individually to each product using a dropdown as the shipment origin address

== Installation ==

**Install and activate the plugin**
To install the plugin, go to your WordPress dashboard, click on 'Plugins' and then 'Add New.' Upload the 'multiple-shipping-options-for-woocommerce.zip' file and click 'Install Now.' Once the installation process completes, click on the 'Activate Plugin' link to activate it.

== Changelog ==

= 1.0.1 =
* Implemented new premium features

= 1.0.0 =
* Initial release

== Upgrade Notice ==
