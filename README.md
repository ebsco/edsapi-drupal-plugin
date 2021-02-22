# A Drupal 8 module for the EBSCO Discovery Services API.

## By EBSCO

  <img src="https://www.ebsco.com/themes/custom/cog_ebsco/logo.svg" width="100" height="100" />

![Packagist](https://img.shields.io/packagist/v/ebsco/edsapi-drupal8-plugin.svg)

## Installation
`$ composer require ebsco/edsapi-drupal8-plugin`

## Usage

#### Configuration

## Contributing

Bug reports and pull requests are welcome on GitHub at https://github.com/ebsco/edsapi-drupal8-plugin.


# Setup 

EBSCO module for Drupal 8
1. Description

The EBSCO module adds the search capabilities of EBSCO Discover service to an existing Drupal 7 installation.

2. Features

2.1. The module implements 3 pages:

    2.1.1. Results page
    2.1.2. Result page
    2.1.3. Advanced Search page

2.2. And 1 blocks: 

    2.2.1. EBSCO Discovery Service
    
2.3. The search capabilities include filters like facets, limiters, expanders, search modes and page options.

3. Installation

    3.1. The EBSCO module can be installed like this:

        3.1.1. Create a folder with name ebsco in your Drupal sites/all/modules directory
        3.1.2. Upload ebsco-drupal.zip to your Drupal sites/all/modules directory
        3.1.3. Unpack ebsco-drupal.zip, it should create the sites/all/modules/ebsco directory
        3.1.4. Login as administrator and go to “Modules”, you should see the “EBSCO Discovery Service” module in the “Others” list of modules
        3.1.5. Check the “ENABLED” checkbox and then press the “Save configuration” button
        3.1.6. Check the Permission to ebsco folder inside of you Drupal App.

    3.2. After the page was refreshed you should see the option to EBSCO Discovery Service on Extend option

    <img src="https://widgets.ebscohost.com/prod/simplekey/drupal8-setup/imgs/img-drupal-setup-001.png"/>

    3.3. Follow the “Configuration” tab link and fill in the requested fields in the “EBSCO Discovery Service settings” form:

    <img src="https://widgets.ebscohost.com/prod/simplekey/drupal8-setup/imgs/img-drupal-setup-002.png"/>

    3.4. Now add the EDS API credentials provided by EBSCO.

    <img src="https://widgets.ebscohost.com/prod/simplekey/drupal8-setup/imgs/img-drupal-setup-003.png"/>

    3.5. Go to Drupal “Structure” menu, then go to “Block Layout” and you will notice one block: “EBSCO Discovery Service”.

    <img src="https://widgets.ebscohost.com/prod/simplekey/drupal8-setup/imgs/img-drupal-setup-004.png"/>

    3.6. Go to the Content and click on Place block

    <img src="https://widgets.ebscohost.com/prod/simplekey/drupal8-setup/imgs/img-drupal-setup-005.png"/>

    3.7. You can setup the module like in the following screenshot:

    <img src="https://widgets.ebscohost.com/prod/simplekey/drupal8-setup/imgs/img-drupal-setup-006.png"/>

    3.8. In the above screenshot the “EBSCO Search Form” block will be embedded in the content of the page.
    3.9. This configuration is best suited for displaying the EBSCO module UI.
    3.10. Press "Save blocks" button in order to submit the changes.
    3.11. These are all the steps required to install the EBSCO module.

If you logout and go to Home page then you should see something like the following screenshot:

<img src="https://widgets.ebscohost.com/prod/simplekey/drupal8-setup/imgs/img-drupal-setup-007.png"/>

In the above screenshot you can see the basic search form at the top of the content.

Below you can see the result page

<img src="https://widgets.ebscohost.com/prod/simplekey/drupal8-setup/imgs/img-drupal-setup-008.png"/>

4. Design

The module is using only the Drupal 8 core , it’s not dependent on other modules. 
The module is using PHP 5.3, like Drupal 8.4.4, so you don’ t have to install or update anything else.
The module is using the PHP session for storing various internal data.
The module was implemented using a default Drupal 8.4.4 installation, with Bartik theme.
The “EBSCO Search Form” blocks is visible for some pages and are hidden for others, the visibility is hard coded in the module’s code. E.g. “EBSCO Search Form” block is not visible on EBSCO advanced search page.

Appendix A
The list of EBSCO module files:

Files EBSCOAPI.php, EBSCOConnector.php, EBSCOResponse.php and sanitizer.class.php are used for performing EDS API requests. They are pretty much independent from Drupal (except for using the Drupal HTTP client).
Files EBSCODocument.php and EBSCORecord.php are used for searching and displaying results, for displaying a single record, for performing an advanced search, etc. They are used in ebsco.module.


Appendix B
Screenshots with EBSCO module pages (Bartik  as default theme):
Empty search page:

<img src="https://widgets.ebscohost.com/prod/simplekey/drupal8-setup/imgs/img-drupal-setup-009.png"/>

Search page with results:

<img src="https://widgets.ebscohost.com/prod/simplekey/drupal8-setup/imgs/img-drupal-setup-010.png"/>

Result page:

<img src="https://widgets.ebscohost.com/prod/simplekey/drupal8-setup/imgs/img-drupal-setup-011.png"/>

HTML Full text page

<img src="https://widgets.ebscohost.com/prod/simplekey/drupal8-setup/imgs/img-drupal-setup-012.png"/>