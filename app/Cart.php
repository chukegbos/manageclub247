<?php

namespace App;

class Cart
{
    public $items = NULL;
    public $totalPrice = 0;
    public $totalQty = 0;
    public $allproduct_id = array();

    public function __construct($oldCart)
    {
        if ($oldCart) {
            $this->items = $oldCart->items;
            $this->totalQty = $oldCart->totalQty;
            $this->totalPrice = $oldCart->totalPrice;
            $this->allproduct_id  = $oldCart->allproduct_id;
        }
    }


    public function add($item, $id){
        $storedItem = ['quantity' => 0, 'price' =>$item->price, 'item' => $item, 'product_id' =>$item->id];
        if ($this->items) {
            if (array_key_exists($id, $this->items)) {
                $storedItem = $this->items[$id];
            }
        }
        
        $storedItem['quantity']++;
        $storedItem['price'] = $item->price * $storedItem['quantity'];
        $this->items[$id] = $storedItem;
        $this->totalQty++;
        $this->totalPrice += $item->price;
        $allproduct_id = array();
        $this->allproduct_id = array_push($allproduct_id,$this->items[$id]['quantity']);
    }

    public function addByOne($id){
        $this->items[$id]['quantity']++;
        $this->items[$id]['price']+=$this->items[$id]['item']['price'];
        $this->totalQty++;
        $this->totalPrice += $this->items[$id]['item']['price'];

        if ($this->items[$id]['quantity'] <= 0) {
            unset($this->items[$id]);
        }
    }

    public function reduceByOne($id){
        $this->items[$id]['quantity']--;
        $this->items[$id]['price']-=$this->items[$id]['item']['price'];
        $this->totalQty--;
        $this->totalPrice -= $this->items[$id]['item']['price'];

        if ($this->items[$id]['quantity'] <= 0) {
            unset($this->items[$id]);
        }
    }

    public function removeItem($id){
        $this->totalQty -= $this->items[$id]['quantity'];
        $this->totalPrice -= $this->items[$id]['item']['price'];
        unset($this->items[$id]);
    }
}
