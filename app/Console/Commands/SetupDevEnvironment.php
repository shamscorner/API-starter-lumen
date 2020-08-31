<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class SetupDevEnvironment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets up the development environment';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Setting up development environment. . .');
        // run migration and seed
        $this->MigrateAndSeedDatabase();
        // get the created user for getting the access token
        $user = $this->getTheCreatedUser();
        // create a personal access client to have the api access
        $this->CreatePersonalAccessClient($user);
        // create a personal access token to have the api access
        $this->CreatePersonalAccessToken($user);
        // finally show a cool message
        $this->info("\nAll done. Happy coding :)\n");
    }

    /**
     * Run migration and necessary seeder
     * 
     * @return null
     */
    private function MigrateAndSeedDatabase()
    {
        $this->call('migrate:fresh');
        $this->call('db:seed');
    }

    /**
     * Get the newly created user in the fresh database
     * 
     * @return User
     */
    private function getTheCreatedUser()
    {
        // create a user
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password')
        ]);
        $this->info("\nJohn Doe user created");
        // show the user's email address, this will help to login later
        $this->warn('User email: ' . $user->email);
        $this->warn('User password: password');

        return $user;
    }

    /**
     * Create a personal access client for a specified user
     * 
     * @param User
     * 
     * @return null
     */
    private function CreatePersonalAccessClient($user)
    {
        $this->call('passport:client', [
            '--personal' => true,
            '--name' => 'Personal Access Client',
            '--user_id' => $user->id
        ]);
    }

    /**
     * Create a personal access token for a specified user
     * 
     * @param User
     * 
     * @return null
     */
    private function CreatePersonalAccessToken($user)
    {
        $token = $user->createToken('Development Token');
        $this->info("\nPersonal access token created successfully");
        $this->warn("Personal access token:");
        $this->line($token->accessToken);
    }
}
