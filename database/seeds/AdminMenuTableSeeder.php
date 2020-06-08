<?php

use Illuminate\Database\Seeder;

class AdminMenuTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('admin_menu')->delete();
        
        \DB::table('admin_menu')->insert(array (
            0 => 
            array (
                'id' => 1,
                'parent_id' => 0,
                'order' => 1,
                'title' => '仪表盘',
                'icon' => 'fa-bar-chart',
                'uri' => '/',
                'permission' => NULL,
                'created_at' => NULL,
                'updated_at' => '2020-06-08 10:09:38',
            ),
            1 => 
            array (
                'id' => 2,
                'parent_id' => 0,
                'order' => 2,
                'title' => '管理设置',
                'icon' => 'fa-tasks',
                'uri' => NULL,
                'permission' => NULL,
                'created_at' => NULL,
                'updated_at' => '2020-06-08 10:08:06',
            ),
            2 => 
            array (
                'id' => 3,
                'parent_id' => 2,
                'order' => 3,
                'title' => '管理员用户',
                'icon' => 'fa-users',
                'uri' => 'auth/users',
                'permission' => NULL,
                'created_at' => NULL,
                'updated_at' => '2020-06-08 10:08:25',
            ),
            3 => 
            array (
                'id' => 4,
                'parent_id' => 2,
                'order' => 4,
                'title' => '管理员角色',
                'icon' => 'fa-user',
                'uri' => 'auth/roles',
                'permission' => NULL,
                'created_at' => NULL,
                'updated_at' => '2020-06-08 10:08:35',
            ),
            4 => 
            array (
                'id' => 5,
                'parent_id' => 2,
                'order' => 5,
                'title' => '管理员权限',
                'icon' => 'fa-ban',
                'uri' => 'auth/permissions',
                'permission' => NULL,
                'created_at' => NULL,
                'updated_at' => '2020-06-08 10:08:49',
            ),
            5 => 
            array (
                'id' => 6,
                'parent_id' => 2,
                'order' => 6,
                'title' => '侧边菜单目录',
                'icon' => 'fa-bars',
                'uri' => 'auth/menu',
                'permission' => NULL,
                'created_at' => NULL,
                'updated_at' => '2020-06-08 10:09:13',
            ),
            6 => 
            array (
                'id' => 7,
                'parent_id' => 2,
                'order' => 7,
                'title' => '操作日志',
                'icon' => 'fa-history',
                'uri' => 'auth/logs',
                'permission' => NULL,
                'created_at' => NULL,
                'updated_at' => '2020-06-08 10:09:26',
            ),
            7 => 
            array (
                'id' => 8,
                'parent_id' => 0,
                'order' => 0,
                'title' => '巨量账户',
                'icon' => 'fa-bars',
                'uri' => '/jl-accounts',
                'permission' => NULL,
                'created_at' => '2020-06-08 10:12:39',
                'updated_at' => '2020-06-08 10:12:39',
            ),
            8 => 
            array (
                'id' => 9,
                'parent_id' => 0,
                'order' => 0,
                'title' => '巨量广告计划数据',
                'icon' => 'fa-bars',
                'uri' => '/jl-advertiser-plan-datas',
                'permission' => NULL,
                'created_at' => '2020-06-08 11:41:31',
                'updated_at' => '2020-06-08 11:41:31',
            ),
        ));
        
        
    }
}