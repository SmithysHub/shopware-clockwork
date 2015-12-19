<?php
namespace Shopware\Plugins\ShopwareClockwork\Clockwork\DataSource;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;

class ShopwareDataSource extends DataSource
{
    protected $context;

    /**
     * ShopwareDataSource constructor.
     * @param $context
     */
    public function __construct($context)
    {
        $this->context = $context;
    }


    /**
     * the entry-point. called by Clockwork itself.
     */
    public function resolve(Request $request)
    {
        $data = $this->context->getData();
        foreach ($data as $name => $item) {
            $request->{$name} =  $item;
        }
//
//        $item = [
//            [ 'data' =>[ 'name' => 'test' , 'data' => 'dataView']],
//            [ 'data' =>[ 'name' => 'test1' , 'data' => [1,2,3]]],
//            [ 'data' =>[ 'name' => 'test2' , 'data' => ['test' => 1, 'test2' => 2]]],
//        ];
//        $request->viewsData =$this->removePasswords(
//                $this->replaceUnserializable($item)
//            );
        return $request;
    }
}