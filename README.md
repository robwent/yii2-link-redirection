# Yii2 Link Redirection Sample Project

This project is intended for people who want to learn Yii2.

The aim is to outline what the resulting app should do, and show the steps that I took to create it.

It is not a walk through project. You should take a look at each step and try to do the work yourself with the links provided. If you run into trouble, each step has a related commit which shows the steps that I took, that you can refer to when building your app.

Feel free to improve upon it.

##The Plan

We all link to websites for further information, but websites change and links get broken.

That's no problem for a website as they can just update the link, but, for an offline publication, it's not possible to correct broken links, so over time, the information quality degrades.

For this reason, we are about to create a link redirection app that will redirect local links to their final destination which can be easily updated.

The aims for this projects functionality are as follows:

* When someone arrives at the site, the requested url is checked against our list of redirected urls. It redirects or shows a 404 error.
* When someone is redirected, we send an event to Google analytics to record how many people arrive at each link.
* We create a command line cron to periodically check all of the links to make sure none are broken.
* If the cron detects a broken link, we use a mail template to email the admin and inform them that a link is broken, with an admin link to update the destination.

##Step 1 : Installing the Basic App Skeleton

Install the Yii2 basic app, via composer, to a web accessible directory by following the instructions on the Github page: [https://github.com/yiisoft/yii2-app-basic](https://github.com/yiisoft/yii2-app-basic)

Make sure that you install the composer asset plugin globally or bower assets such as Bootstrap and jQuery won't get installed. The install would seem broken without it.

In this repository, the application is in the linkapp folder.

----------
Once installed, browse to the web folder and you should see a basic app.
![](images/fresh-install.PNG)

Yii2 is installed.

##Step 2 : Configure a database

Create an mysql database and update the config file with the correct information.

Follow the steps for the basic app to configure the database
[https://github.com/yiisoft/yii2-app-basic#database](https://github.com/yiisoft/yii2-app-basic#database)

If you want to use an sqlite database you can create one and configure the app to connect to that. There have been a few issues with people trying to connect so it is always best too use the app root alias to connect as it makes the app portable in the future without having to change the database connection information.

If I store my sqlite db in a folder called 'db' then I connect using the following:

`'dsn' => 'sqlite:@app/db/linkapp.db',`

Yii now knows where the database is, so we need to add some tables and generate some code using Gii.

##Step 3 : Add our Tables Using Migrations

The tables could be added manually to the database without this step,but if you want to keep track of changes, or you work in a team, then migrations are the way forward.

Review the docs regarding migrations here : [http://www.yiiframework.com/doc-2.0/guide-db-migrations.html](http://www.yiiframework.com/doc-2.0/guide-db-migrations.html)

Create 2 tables, links and settings.

Links has the fields:
- id (int)
- short_url (varchar(45))
- full_url - (text)
- status (boolean)
- description (text)
- published (dtaetime)

Settings has the fields:
- id (int)
- setting_name (varchar(255))
- setting_value (text)
- setting_type (varchar(255))

**Note:** To generate crud for a table using Gii, a table *needs* to have a primary key.

If you get stuck, take a look at the commit for this part and check your migration against it.

yii migrate/create create_links_table

yii migrate/create create_settings_table

Run the migrations to create the database tables with the console command:

`yii migrate up`

Check your database, and you should now have the links and settings tables, plus the migrations table that Yii uses to keep track of database updates.

##Step 4 : Pretty URL's

To use nice urls, we need to take 2 steps.

- Create an .htaccess file.
- Tell Yii to use nice urls.

In the web root, create a .htaccess file and add the following:

    Options +FollowSymLinks
    IndexIgnore */*

    RewriteEngine on

    # if a directory or a file exists, use it directly
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d

    # otherwise forward it to index.php
    RewriteRule . index.php

All requests that don't match an existing file will now be redirected to the index.php file for the application to deal with.

In your config/web.php file, uncomment the urlManager section to enable nice urls.

    'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],

You should now be able to browse to the included pages using nice urls like /site/about

##Step 5 : Generating Models and Crud

We now have the tables we need, so we can start to generate the base code using the tool: Gii.

Use the login link to log in to the app as the admin user with admin/admin. We will change these details later.

As we have pretty urls enabled now, you should be able to browse to the url:

`yourlocation/gii`

This should be enabled by default if you are working on a localhost.

If you are working on a live site then you will need to configure Gii to allow you access in your configuration file. Check the docs.

We first need to generate a model to be able to create the crud that we need.

Select the 'Model Generator'

Generate models.

First steps:

Table Name: It should auto complete so select the table 'links'.

The model class will be generated for you.

Leave all as default, but select 'Generate activeQuery' as we will use that soon.

Click preview and then generate.

Generated files will now be in the models folder.

----------

Click on the Gii CRUD option in the left menu.

For the model class field, it needs to be relative to our new Links class:

app\models\Links

Search class:

app\models\LinksSearch

Controller:

app\controllers\LinksController

View Path:

@app/views/links

Check pjax, just for fun.

Check I18n if you want to translate the app into other languages.

Click preview and generate.

You should now be able to view the link admin at @webroot/links.

You can now add, edit, update and delete new link records.


##Step 6 : Modifying the Generated Code

###Updating the form and redirect behaviour

Open the _form.php partial in /views/links/ and change the 'status' and 'published' fields from a text input, to a checkbox. [http://www.yiiframework.com/doc-2.0/yii-widgets-activefield.html#checkbox()-detail](http://www.yiiframework.com/doc-2.0/yii-widgets-activefield.html#checkbox()-detail)

Now, when you view the /links page, and click on the 'Create Links' button, you should see the form has a checkbox for these 2 fields, rather than the previous text fields.

If you now create some links, you will see that after creating a link, you are redirected to a view of the record that we just created.
This isn't that useful to us, as all the information we need to view is also shown in the grid overview.

Open up controllers/LinksController.php and find the actionCreate method.

In the if statement which checks to see if a new record was saved, change the line:

`return $this->redirect(['view', 'id' => $model->id]);`

to redirect back to the index page:

`return $this->redirect(['index']);`

Also change the redirect for the update action to do the same thing.

Now try creating a new link and you should be redirected to the index page on save.

###Updating the index list view

In the listings view, we have no need in the id column or the view record icon link, so we should take those out.

Open views/links/index.php and in the 'columns' array, remove id.

To change the available icons, we need to add a template to the 'yii\grid\ActionColumn' class array.

http://www.yiiframework.com/doc-2.0/yii-grid-actioncolumn.html#$template-detail

Add a template to only include {update} {delete} actions.

We now have a working administration area for our links. 

##Step 7 : Making the Admin Area Only Accessible to Authenticated Users

We can now manage our links, but if you log out of the application, you will see that you can still access the the link area and add/update and delete records.

Update the LinksController 'behaviors' function to add access control rules.

Start by adding a 'use' statement to the top of the file so Yii knows where to find the accessControl class

`use yii\filters\AccessControl;`

Allow guest users to perform 'login', 'error' and 'view' actions.

Allow authenticated users to perform 'logout', 'index', 'create', 'update', 'linkDetail', 'delete' actions by specifying the roles with @ (Authenticated user).

[http://www.yiiframework.com/doc-2.0/yii-filters-accesscontrol.html](http://www.yiiframework.com/doc-2.0/yii-filters-accesscontrol.html)

If you now try going to the links listing page, you should now be redirected to the site login page.

If you then login with the details admin/admin, you should be authenticated and then redirected to the page you were trying to access.

###Changing the login details and login page

As you probably noticed, the login details are shown underneath the form on the login page.

Update views/site/login.php to remove the user details.

Update models/User.php to update the admin user details and remove the demo user












