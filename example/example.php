<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use NilPortugues\Example\Domain\UserId;
use NilPortugues\Example\Persistence\Eloquent\User;
use NilPortugues\Example\Persistence\Eloquent\UserRepository;
use NilPortugues\Example\Service\UserAdapter;
use NilPortugues\Foundation\Domain\Model\Repository\Filter;
use NilPortugues\Foundation\Domain\Model\Repository\Order;
use NilPortugues\Foundation\Domain\Model\Repository\Sort;

include_once __DIR__.'/../vendor/autoload.php';

//-------------------------------------------------------------------------------------------------------------
// - Create database if does not exist
//-------------------------------------------------------------------------------------------------------------
$capsule = new Capsule();

//Adds MongoDb support.
$capsule->getDatabaseManager()->extend('mongodb', function ($config) {
    return new Jenssegers\Mongodb\Connection($config);
});

//Create connection
$capsule->addConnection([
    'driver' => 'mongodb',
    'host' => 'localhost',
    'port' => 27017,
    'database' => 'users',
    'username' => '',
    'password' => '',
    'options' => [
        'db' => 'admin',
    ],
],
    'default'
);
$capsule->bootEloquent();
$capsule->setAsGlobal();

//-------------------------------------------------------------------------------------------------------------
// - Create dummy data for the same of the example.
//-------------------------------------------------------------------------------------------------------------
User::query()->delete();

$model = new User();
$model->id = 1;
$model->name = 'Admin User';
$model->created_at = '2016-02-18';
$model->save();

for ($i = 2; $i <= 20; ++$i) {
    $model = new User();
    $model->id = $i;
    $model->name = 'Dummy User '.$i;
    $model->created_at = (new DateTime())->setDate(2016, rand(1, 12), rand(1, 27));
    $model->save();
}

//-------------------------------------------------------------------------------------------------------------
// - getUserAction
//-------------------------------------------------------------------------------------------------------------
$userAdapter = new UserAdapter();
$repository = new UserRepository($userAdapter);

$userId = new UserId(1);
print_r($repository->find($userId));

//-------------------------------------------------------------------------------------------------------------
// - getUsersRegisteredLastMonth
//-------------------------------------------------------------------------------------------------------------

$filter = new Filter();
$filter->must()->notIncludeGroup('id', [2, 5]);
$filter->must()->beGreaterThan('created_at', new DateTime('2016-03-01'));

$sort = new Sort();
$sort->setOrderFor('created_at', new Order('ASC'));

print_r($repository->findBy($filter, $sort));
