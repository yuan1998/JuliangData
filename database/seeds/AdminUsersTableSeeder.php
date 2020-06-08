<?php

use Illuminate\Database\Seeder;

class AdminUsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('admin_users')->delete();
        
        \DB::table('admin_users')->insert(array (
            0 => 
            array (
                'id' => 1,
                'username' => 'admin',
                'password' => '$2y$10$gnkepFZmhtyc/ugS8tbJNOK8PExVjb9tIsywmvtoGlcwtblNZPO.2',
                'name' => 'Administrator',
                'avatar' => NULL,
                'remember_token' => 'x8eRBatjPw1qC466tjQh8oGQyrnFWfgkQeUCMC9jSkFZ6J4pPScFSimxvezp',
                'created_at' => '2020-06-08 10:01:22',
                'updated_at' => '2020-06-08 10:01:22',
            ),
        ));
        
        
    }
}