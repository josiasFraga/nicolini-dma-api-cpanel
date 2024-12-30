<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * AppProductUsersFixture
 */
class AppProductUsersFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'created' => 1733181436,
                'updated' => 1733181436,
                'user_login' => 'Lorem ipsum dolor sit amet',
                'app_product_id' => 1,
            ],
        ];
        parent::init();
    }
}
