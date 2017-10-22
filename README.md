# Yii2 Link Redirection Sample Project

This project is intended for people who want to learn Yii2.

The aim is to outline what the resulting app should do, and show the steps that I took to create it.

It is not a walk through project. You should take a look at each step and try to do the work yourself with the links provided. If you run into trouble, each step has a related commit which shows the steps that I took, that you can refer to when building your app.

Feel free to improve upon it.

## The Plan

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

[http://www.yiiframework.com/doc-2.0/ext-gii-index.html](http://www.yiiframework.com/doc-2.0/ext-gii-index.html)

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

Open the _form.php partial in /views/links/ and change the 'status' field from a text input, to a checkbox. [http://www.yiiframework.com/doc-2.0/yii-widgets-activefield.html#checkbox()-detail](http://www.yiiframework.com/doc-2.0/yii-widgets-activefield.html#checkbox()-detail)

Now, when you view the /links page, and click on the 'Create Links' button, you should see the status field uses a checkbox, rather than the previous text field.

Remove the 'published' field as we will populate this automatically later.

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

##Step 8 : Modifying Default Routes and Hiding the Navbar

There won't be any need for guest users to see the admin bar, so we will change the layout to only show the navbar to authenticated users.

Before we do that, we should change the the login url from /site/login to just /login so it's easier to remember.

[http://www.yiiframework.com/doc-2.0/yii-web-urlmanager.html#$rules-detail](http://www.yiiframework.com/doc-2.0/yii-web-urlmanager.html#$rules-detail)

Add the rule `'<alias:login|logout>' => 'site/<alias>',` to set login and logout as aliases of the real urls (Note: The login page will still be accessible by the original /site/login path and is purely for our convenience).

Whilst we are in the urlManager configuration, add 3 more rules so the full block looks like this:

    'rules' => [
              'links' => 'links/index',
              'settings' => 'settings/update',
              '<alias:login|logout>' => 'site/<alias>',
              '<short_url>' => 'links/view',
            ],

The settings page that we create in the next section will only have an update page, so we want the /settings url to automatically go to the update action we will specify in the controller.

Yii tests the rules in order, so we want to check the pages we know exist first, and the last rule will be used to pass the url to our links controller, view action, which can then check to see if the short_url passed through the url matches any in our links database table.

###Hiding the navbar

The main navbar as added as a Yii widget in the main layout file at /views/layouts/main.php

Modify the NavBar function settings to:

- Change the brand label text to something more suitable.
- Remove the 'home', 'about' and 'contact' links from the items array. Replace them with 'Links' and 'Settings' to the routes we juct created in the web config file. 
- Copy the guest user check to wrap the whole navbar to only show it to logged in users.
- Change the login/out logic to only include a logout option.

You should now only see the navbar after navigation to /login and logging in to the application, and it should now include a link to the Links index page, and a link to the settings page (Which will currently go to a 404 error).

##Step 9 : Creating the Settings Page

The settings page will allow us to modify some global options. These could also be stored and read from a file, but we will use Gii to generate a model, controller and update view to modify the settings table we created earlier, and then we will create a settings class and bootstrap it so that our options are always available for us to use in the application.

###Generating the controller and view

Make sure you are logged into the application and navigate back to /gii to the options page.

Select the model generator to create a model for the settings table.

As we only need an update view, use the Controller Generator rather than the CRUD Generator.

Controller class: app\controllers\SettingsController

Action IDs: update

View Path: @app/views/settings

Preview and generate the controller and view.

Now click on the application link in the top menu to get back to our application and then click on the settings link in the top menu. You should now see our newly created view file with some dummy text.

###Adding some data to the settings table

Before we create the settings page, it's going to be helpful to have some data in the settings table to work with. We can do this by creating a new migration to seed the settings table.

Navigate to the root of the site in a command prompt and create the migration file:

`yii migrate/create seed_settings`

Navigate to the migration file and change the up method to the following

    public function up()
    {
      $this->batchInsert('settings', [
        'setting_name', 'setting_value', 'setting_type'
      ],
      [
        [
          'robots',
          'noindex,follow',
          'text',
        ],
        [
          'statuscode',
          '303',
          'text',
        ],
        [
          'log404',
          1,
          'checkbox',
        ],
        [
          'analytics',
          null,
          'text',
        ],
        [
          'mailto',
          null,
          'text',
        ]
      ]);
    }


Then apply the migration with the command `yii migrate`.

If you now check the settings table in the database, it should contain our new settings data.

###Adding the form to the settings update page

There are many ways that the settings page could be displayed.

The reason that we added the `setting_type` column to the settings table was so that we could use it to loop through each setting and display the form with minimal code. You could of course ignore that and create each field manually by using the setting_name value.

Take a look at /vies/settings/update.php and copy the code to display the fields.

We use Yii's html helper to create the form button, and the activeForm widget to create the actual form.

If you try and view the settings page now you will get an error. We first need to pass the settings to the view through our generated controller.

###Passing the settings from the database to the update page

Open up the generated settingsController.php file

Copy the behaviors function from the linksController so the settings are only available to authenticated users and update the actions accordingly.

Add the following use statements to the top of the file so we can access the parts of Yii we need and our settings model class

    use Yii;
    use yii\base\Model;
    use yii\web\Controller;
    use app\models\Settings;
    use yii\filters\VerbFilter;
    use yii\filters\AccessControl;

In the actionUpdate method, we first need to get all the settings from the database to pass to the view template.

We will do this using activeRecord: [http://www.yiiframework.com/doc-2.0/yii-db-activerecord.html](http://www.yiiframework.com/doc-2.0/yii-db-activerecord.html)

`$settings = Settings::find()->indexBy('id')->all();`

This uses the Settings class of our model (Specified in the use statements at the top of the file) and the find method, which in turn uses the SettingsQuery class generated by Gii's model generator.

Update the return statement to pass the settings to the view file as an array:

`return $this->render('update', ['settings' => $settings]);`

Now, if you load the settings page, you should see the fields with our seeded data values.

Although we can view the settings, changing them won't update the database. We need to update the controller action to check if the form has been submitted.

    $settings = Settings::find()->indexBy('id')->all();

        if (Model::loadMultiple($settings, Yii::$app->request->post()) && Model::validateMultiple($settings)) {
            foreach ($settings as $setting) {
                $setting->save(false);
            }
        }

        return $this->render('update', ['settings' => $settings]);

We use Yii's model class to check if the form has been submitted, and validate multiple to check that each setting conforms to the rules specified in the settings model's rules method. If the checks pass, then we save each setting to the database and then render the view again.

You should now be able to update the settings using the new form.

###Making the settings available in the application

Now we have the settings in the database, we need to make them available to the application at runtime.

Create the file /base/settings.php and copy the contents of the file in the repository.

This new settings class uses Yii's bootstrapInterface class to hook into the global app parameters and add our settings to a new settings array.

Add the class to the bootstrap section of the web config file:

    'bootstrap' => [
      'log',
      'app\base\settings',
    ],

Our settings are now available and can be accessed using:

`Yii::$app->params['settings']['setting_name']`

##Step 10 : Redirecting the Links

We now have a working admin area where we can add our short urls, we have our routes set to redirect all urls that don't match our defined pages to send all other urls to our link controller and view action. We can now start checking the urls and redirect them.

Make sure you have a few records added for testing later.

###The controller

Open up the links controller and check the view method. It should look like this:

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

Since we will not be checking for the id of a specific record and will be checking by the shor_url field, we can strip this back to remove all code

    public function actionView()
    {
        
    }

Firstly, we want to get the url path, which our route defines as a get variable to pass to the controller (The brackets pass the url as a get parameter <short_url>).

`$short_url = Yii::$app->request->get('short_url', false);`

We then use activeRecord from the Links model to search for a record match.

`$link = Links::findOne(['short_url' => $short_url]);`

If no record exists $link will be false. We can use this to either direct the user to the view template, passing the record as an array to the view, or generate a 404 page. We will also check the record retrieved to make sure that status === 1 so we know the redirect is enabled.

    public function actionView()
    {
        //Check the url as defined by our route
        $short_url = Yii::$app->request->get('short_url', false);
        //Check if we have a matching record
        $link = Links::findOne(['short_url' => $short_url]);
        //If we have a match, go to the view page, if not throw a 404 and pass the url as the message
        if ($link && (int)$link->status === 1) {
          return $this->render('view', [
            'model' => $link,
          ]);
        } else {
          throw new \yii\web\NotFoundHttpException($short_url);
        }
    }

###The view

Open up the views/links/view.php file and remove everything other than the `use yii\helpers\Html;` statement. We will only be redirecting the user here so no output is necessary.

In our links controller, we just passed the first matching record to the view as 'model' so all fields from the record are available in the view through the $model variable.

`$full_url = $model->full_url;`

We also have our settings available, so we can now redirect the user to the full_url, adding a status code and robots header if the values are set.

Check your code against the repository and then test the app.

Go to appaddress/made_up_short_url and you should be shown a 404 message with the requested url passed into the error message.

Then check the site for a valid short_url in your links table /appaddress/valid_short_url and you should be redirected to the full_url field for the same record.

###Adding Google analytics

To record the number of hits to each of our links, we can use Google analytics and events.

Since the page will redirect without rendering any content, we will use the measurement protocol to send the information using curl

[https://developers.google.com/analytics/devguides/collection/protocol/v1/reference](https://developers.google.com/analytics/devguides/collection/protocol/v1/reference)

To record a hit as an analytics 'event' we need to specify the following variables:

- User identifier (not personally identifiable such as an IP address)
- The account id
- The hit type (event)
- The event category
- The event action

To create a unique id for each user, we can use php's crc32 function on the users ip address. The analytics id will be set in our settings page.

Check the file in the repository for the full code.

##Step 11 : Checking all Links by CRON

Now we have the link redirection working, we should check periodically to see if the links work, or if they need updating.

The Yii2 basic skeleton app comes with a console application pre-configured which we can use via a cron job to check our links.

The configuration file can be found at /config/console.php

It specifies the controller namespace as app\commands, which corresponds to the commands folder which contains an example hello world controller, which you can test from the root of the site with the command:

`yii hello/index`

(you could also use `yii hello` here as index is the default action)

We will be needing some of our settings from the database for the link checking, so add our settings class to the bootstrap area of the console configuration file, in the same way as we did for the web config.

We will also need the same url routes to be able to create links for our emails, so also copy over the urlManager section. Add a new parameter to the urlManager for 'baseUrl' specifying the url of the site (We will use this to generate edit links to modify any records that contain broken links)

###The controller

Create the file LinkController.php in the commands folder by copying and modifying the HelloController. We will need the following use statements:

    use Yii;
    use app\models\Links;
    use yii\console\Controller;
    use yii\helpers\Html;
    use yii\helpers\Url;

Yii to get our settings, the links model to get all of our links from the database, the html helper to generate edit links, and url to create those links back to the application.

Create a public function for actionChecklinks which we will later call using `yii link/checklinks`

In this function you need to:

- Get all link records where status is set to 1 using the links model class and activeRecord.
- Initialise an array to hold any errors.
- Loop through each record and use curl to check the response headers returned by the host server.
- If the status code is not 200 (ok), create an edit link using Yii's helper classes and add an error message to the errors array containing the edit link. 

[http://www.yiiframework.com/doc-2.0/yii-helpers-html.html](http://www.yiiframework.com/doc-2.0/yii-helpers-html.html)

[http://www.yiiframework.com/doc-2.0/yii-helpers-url.html](http://www.yiiframework.com/doc-2.0/yii-helpers-url.html)

After looping through the links, check the global settings to see if we have a value for 'mailto' (You will need to update the settings page to actually add this).

If the errors array is not empty, and we have a mailto address, we can send an email to the user to notify them of the broken links using the built-in swiftmailer.

[http://www.yiiframework.com/doc-2.0/ext-swiftmailer-index.html](http://www.yiiframework.com/doc-2.0/ext-swiftmailer-index.html)

Make sure you have some full urls in the links table that don't work and then test the checks using the command `yii link/checklinks`.

If you get a lot of warning messages, you can turn off debugging in the yii file in the root of the app by setting YII_DEBUG to false.

By default, the output of the mailer will be saved to the folder 'mailoutput' in the root of the site. Use the link above to configure swiftmailer to send real mail.

##Step 12 : Enhancing Grid View with an Extension

We now hav a functional app, but updating links is harder than it should be and our gridview shows 1 or 0 for the status column.

We can replace the standard gridView widget with another that provides extra functionality such as inline editing. We will use the Kartik yii2-grid widget.

[https://github.com/kartik-v/yii2-grid](https://github.com/kartik-v/yii2-grid)

We will need to add 2 new entries to the composer.json files require section:

    "kartik-v/yii2-grid": "@dev",
    "kartik-v/yii2-editable": "@dev"

Then run `composer update` from command line in the root of the app.

You shoudl now have karti-v folder in the vendor folder.

Follow the instructions to configure the extension in the Github repository by editing the congiguration file and add the use statements to the views/links/index.php file

    use kartik\grid\GridView;
    use kartik\editable\Editable;

Note: if you are copying the code from this repository, you will need oto create the new partial file _link-details.php

##Step 13 : Use Behaviours to Add a Timestamp to the Create Method

We currently have a 'published' field of type datetime that is empty for all records in our links table. We can add a behaviour to our links model to automatically populate this field when a record is created.

[http://www.yiiframework.com/doc-2.0/guide-concept-behaviors.html](http://www.yiiframework.com/doc-2.0/guide-concept-behaviors.html)

Add a behaviour method to the links controller to populate the 'published' field with the class `TimestampBehavior` on the activeRecord event `EVENT_BEFORE_INSERT`. Use the php date function to format the current time to a valid datetime format for the database type.