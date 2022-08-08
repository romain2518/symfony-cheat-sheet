<?php
namespace Deployer;

require 'recipe/symfony.php';

// --------------------------
// Default settings
// --------------------------

set('composer_options', '--verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader');

// ---------------------------------------------------------------------------
// Application settings
// ---------------------------------------------------------------------------
// TODO
set("env_database", "mysql://user:password@127.0.0.1:3306/database?serverVersion=mariadb-10.3.34");

// Add any parameter you need in your .env.local file
// set("env_api", "xxxxxx");

// ---------------------------------------------------------------------------
// Remote server login settings
// ---------------------------------------------------------------------------

// Name of the file containing the SSH key used to authenticate with the remote server (sometimes optional)
// set('ssh_key_filename', 'nom_du_fichier_contenant_la_cle_ssh.pem');

// Remote server address (IP address or public DNS)
// TODO 
set('remote_server_url','ip_address_or_public_dns');

// Name of the server user
// TODO
set('remote_server_user','server-user');

// ---------------------------------------------------------------------------
// Specific deployment settings
// ---------------------------------------------------------------------------

// Targeted directory
// TODO
set('remote_server_target_repository', '/var/www/html/my_project');

// Github repository adress
// TODO
set('repository', 'git@github.com:my_username92/my-super-project.git');

// Name of the branch to deploy
// TODO branch
set('repository_target_branch', 'master');

// ---------------------------------------------------------------------------
// Other deployment settings
// ---------------------------------------------------------------------------

// [Optional]
// Show "git clone" command return (true => yes, false => no)
set('git_tty', true); 

// Send stats to Deployer.org
set('allow_anonymous_stats', false);

// How many releases will be kept (Default: 5, Unlimited: -1)
// TODO
set('keep_releases', 3);

// ---------------------------------------------------------------------------
// Deployment settings for the 'production' server
// ---------------------------------------------------------------------------

host('prod')
    ->set('hostname', '{{remote_server_url}}')
    ->set('deploy_path', '{{remote_server_target_repository}}')
    ->set('branch', '{{repository_target_branch}}')
    ->set('remote_user', '{{remote_server_user}}');
    // Uncomment if you use SSH key to authenticate with the remote server
    // ->set('identity_file','~/.ssh/{{ssh_key_filename}}')

// ---------------------------------------------------------------------------
// Tasks definition 
// ---------------------------------------------------------------------------

desc('Create database');
task('init:database', function() {
    run('{{bin/console}} doctrine:database:create');
});

desc('Drop database');
task('init:database:drop', function() {
    run('{{bin/console}} doctrine:database:drop --if-exists --no-interaction --force');
});

// TODO
// If you want to load your fixtures
// desc("Load fixtures");
// task('init:fixtures', function () {
//     run('yes | {{bin/console}} doctrine:fixtures:load');
// });

// TODO
// If you use lexik JWT
// desc("Create JSON Web Token (JWT)");
// task('init:jwt', function () {
//     run('{{bin/console}} lexik:jwt:generate-keypair');
// });

// TODO
// If you need to go in DEV env to install dependencies
//? (Note: You will also need to move 'init:config:write:prod' down after lines requiring DEV env)
// desc('overwrite the .env.local THEN write the DEV settings');
// task('init:config:write:dev', function() {
//     run('echo "APP_ENV=dev" > {{remote_server_target_repository}}/shared/.env.local');
//     run('echo "DATABASE_URL={{env_database}}" >> {{remote_server_target_repository}}/shared/.env.local');
// });

desc('overwrite the .env.local THEN write the PROD settings');
task('init:config:write:prod', function() {
    run('echo "APP_ENV=prod" > {{remote_server_target_repository}}/shared/.env.local');
    run('echo "DATABASE_URL={{env_database}}" >> {{remote_server_target_repository}}/shared/.env.local');
});

// TODO: Check tasks and uncomment the ones you need
desc('Deploy project');
task('first_deploy', [

    // https://deployer.org/docs/7.x/recipe/common#deployprepare
    'deploy:prepare',

    // Generate .env.local in DEV env
    // 'init:config:write:dev',

    // Generate .env.local in PROD env
    'init:config:write:prod',

    // https://deployer.org/docs/7.x/recipe/deploy/vendors#deployvendors
    'deploy:vendors',

    // https://deployer.org/docs/7.x/recipe/symfony#deploycacheclear
    'deploy:cache:clear',

    // In case the database already exists
    'init:database:drop',

    // Create database
    'init:database',

    // https://deployer.org/docs/7.x/recipe/symfony#databasemigrate
    'database:migrate',

    // Load fixtures
    // 'init:fixtures',

    // Generate lexik:jwt
    // 'init:jwt',

    // https://deployer.org/docs/7.x/recipe/common#deploypublish
    'deploy:publish'
]);

task('prod_update', [
    // https://deployer.org/docs/7.x/recipe/common#deployprepare
    'deploy:prepare',

    // https://deployer.org/docs/7.x/recipe/deploy/vendors#deployvendors
    'deploy:vendors',

    // https://deployer.org/docs/7.x/recipe/symfony#deploycacheclear
    'deploy:cache:clear',

    // https://deployer.org/docs/7.x/recipe/symfony#databasemigrate
    'database:migrate',
    
    // https://deployer.org/docs/7.x/recipe/common#deploypublish
    'deploy:publish'
]);