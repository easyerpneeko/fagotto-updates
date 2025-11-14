<?php

namespace App\models_local\Cafeteria;

use Illuminate\Database\Eloquent\Model;
use App\models_local\Order;
use App\models_local\Board;
use App\models_local\Waiter;
use App\models_local\Sell;
use Illuminate\Support\Facades\Log;

// Helpers
use App\Helpers\CurrentApp;

class OrderKitchen extends Model
{
    protected $connection = 'mysql_local';
    protected $fillable = [
      'order_id',
      'sell_id',
      'products', // [{ id, name, quantity, cecina }]
      'kitchen_state', // ["pending","process","complete","cancelled", "closed"]
      'completed_at',
      'random_color'
    ];

    protected $appends = ['productos'];

    // Relaciones

    public function order() {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    // Getters

    public function getProductosAttribute() {
        if (!$this->products) return [];
        return json_decode($this->products, 1);
    }

    // Setters

    public function setProductosAttribute($value) {
        $this->products = json_encode($value);
        return $this;
    }

    // Aux

    public static function orderProductsToKitchenProducts($productsInput) {
        $products = [];

        if (is_string($productsInput)) {
            $productsInput = json_decode($productsInput, 1);
        }

        foreach ($productsInput as $productInput) {
            $products[] = Self::createProduct(
                (integer) $productInput["id"],
                (string) $productInput["name"],
                (float) $productInput["quantity"],
                (boolean) (isset($productInput["cecina"]) ? $productInput["cecina"] : false)
            );
        }

        return $products;
    }

    public static function createProduct($id, $name, $quantity = 0, $cecina = false) {
        return [
            "id" => $id,
            "name" => $name,
            "quantity" => $quantity,
            "cecina" => $cecina,
            "done" => false,
        ];
    }

    public static function createNew($arrayInput = []) {
        $itemToSave = [];

        $keysAllow = [
            'order_id',
            'sell_id',
            'products',
            'kitchen_state',
            'completed_at'
        ];

        if (isset($arrayInput['products']) && $arrayInput['products'] && !is_string($arrayInput['products'])) {
            $arrayInput['products'] = json_encode($arrayInput['products']);
        }

        foreach ($keysAllow as $key) {
            if (isset($arrayInput[$key])) $itemToSave[$key] = $arrayInput[$key];
            else $itemToSave[$key] = null;
        }
        
        if (CurrentApp::ConfStr('modulos.cafeteria.submodulo.modo_cocina.ajustes.blanco_y_negro')) {
            $itemToSave['random_color'] = "#272727";
        }else{
            $itemToSave['random_color'] = Self::randomHexadecimalColor();
        }

        return OrderKitchen::create($itemToSave);
    }

    public static function randomHexadecimalColor() {
        return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

    // Methods
    public function markAsComplete() {
        $this->update([
            'kitchen_state' => 'complete',
            'completed_at' => Date('Y-m-d H:i:s')
        ]);
    }

    public function markAsCancelled() {
        $this->update([ 
            'kitchen_state' => 'cancelled',
            'order_id' => null
        ]);
    }

    public function markAsClosed() {
        $this->update([
            'kitchen_state' => 'closed',
            'completed_at' => Date('Y-m-d H:i:s')
        ]);
    }

    public function markAsProcess() {
        $this->update(['kitchen_state' => 'process', 'completed_at' => null]);
    }

    public function markAsPending() {
        $this->update(['kitchen_state' => 'pending', 'completed_at' => null]);
    }

    public function addOfflineProduct($product) { // {name}
        $this->addProduct($product, 0);
    }

    public function setOfflineProducts($products) { // [{name}]
        $this->setProducts($products, 0);
    }

    public function addProduct($product, $saveProducts = 1) { //{name}
        $productos = $this->productos;

        $encontrado = false;

        foreach ($productos as $pKey => $oldProducto) {
            if ($oldProducto['id'] === $product['id']) {
                $productos[$pKey]["quantity"] += (float) $product["quantity"];
                $encontrado = true;
            }
        }

        if (!$encontrado) {
            $productos[] = $product;
        }
        
        $this->productos = $productos;

        if ($saveProducts) $this->update(['products' => $this->products]);
        return $this;
    }

    public function setProducts($products, $saveProducts = 1) { //[{name}]
        $this->productos = $products;
        if ($saveProducts) $this->update(['products' => $this->products]);
        return $this;
    }

    public function setOrder(Order $order) {
        $this->update(['order_id' => $order->id]);
        return $this;
    }

    public function setSell(Sell $sell) {
        $this->update(['sell_id' => $sell->id]);
        return $this;
    }
}
/*
    [
        {
            id: 3,
            name: "Zapatos de cecina",
            quantity: 4,
            cecina: true,
            done?: false //
        }
    ]
*/