<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;

class CartController extends Controller
{
    public function addToCart(Request $request) {
        $product = Product::with('product_images')->find($request->id);

        if ($product == null) {
            return response()->json([
                'status' => false,
                'message' => 'Record Not Found'
            ]);
        }

        if (Cart::count() > 0) {
             
            $cartContent = Cart::content();
            $productAlreadyExist = false;

            foreach ($cartContent as $item) {
                if ($item->id == $product->id) {
                    $productAlreadyExist = true;
                }
            }

            if ($productAlreadyExist == false) {
                Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '' ]);
                $status = true;
                $message = '<strong>'.$product->title.'</strong> added in your cart successfully.';
                session()->flash('success', $message);

            } else {
                $status = false;
                $message = $product->title.' already added in Cart';
            }


        } else {
            echo "Cart is Empty Now Adding a product in cart";
            Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '' ]);
            $status = true;
            $message = '<strong>'.$product->title.'</strong> added in your cart successfully.';
            session()->flash('success', $message);
        }

        return response()->json([
                'status' => $status,
                'message' => $message
            ]);
    }

    public function cart() {
        $cartContent = Cart::content();
        //dd($cartContent);
        $data['cartContent'] = $cartContent;
        return view('front.cart', $data);
    }

    public function updateCart(Request $request) {
        $rowId = $request->rowId;
        $qty = $request->qty;

        $itemInfo = Cart::get($rowId);

        $product = Product::find($itemInfo->id);
        //Check QTY Available in Stock
        if ($product->track_qty == 'Yes') {
            if ($qty <= $product->qty) {
                Cart::update($rowId, $qty);
                $message = 'Cart updated successfully';
                $status = true;
                session()->flash('success', $message);
            } else {
                $message = 'Requested qty ('.$qty.') not available in stock.';
                $status = false;
                session()->flash('error', $message);
            }
        } else {
            Cart::update($rowId, $qty);
                $message = 'Cart updated successfully';
                $status = true;
                session()->flash('success', $message);
        }
        
        
        return response()->json([
            'status' => $status,
            'message' => $message
        ]);

    }

    public function deleteItem(Request $request) {

        $itemInfo = Cart::get($request->rowId);

        if($itemInfo == null) {
            $errorMessage = 'Item not found in cart';
            session()->flash('error', $errorMessage);

            return response()->json([
                'status' => false,
                'message' => $errorMessage
            ]);
        }

        Cart::remove($request->rowId);

        $message = 'Item removed from cart successfully';
        session()->flash('success', $message);
            return response()->json([
                'status' => true,
                'message' => $message
            ]);


        
    }
}