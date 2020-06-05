<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Image;
use App\Product;
use App\Category;
use Auth;
use Session;

class ProductsController extends Controller
{
    public function addProducts(Request $request){

        if($request->isMethod('post')){
            $data = $request->all();

            //echo "<pre>"; print_r($data); echo "<pre>";
            //die;

            $product= new Product;
            if(empty($data['category_id'])){
                return redirect()->back()->with('flash_message_error', 'Category not selected.');
            }
            $product->category_id = $data['category_id'];
            $product->product_name = $data['product_name'];
            $product->product_color = $data['product_color'];
            $product->product_code = $data['product_code'];
            $product->price = $data['price'];
            $product->description = $data['description'];
            if(empty($data['description'])){
                $product->description = 'Not specified';
            }

            if($request-> hasFile('image')){
                $image_temp = $request->file('image');
                if($image_temp-> isValid()){
                    $extension = $image_temp -> clientExtension();
                    $filename = rand(111, 99999).'.'.$extension;
                    $large_image_path = 'images/backend_images/products/large/'.$filename;
                    $medium_image_path = 'images/backend_images/products/medium/'.$filename;
                    $small_image_path = 'images/backend_images/products/small/'.$filename;
                    
                    Image::make($image_temp)->save($large_image_path);
                    Image::make($image_temp)->resize(600,600)->save($medium_image_path);
                    Image::make($image_temp)->resize(300,300)->save($small_image_path);
                    
                    $product->image = $filename;

                }

            }

            $product->save();
            return redirect('/admin/view-products')->with('flash_message_success', 'Product uploaded successfully');

  
        }
        
        $categories = Category::where(['parent_id'=>0])->get();
        $categories_dropdown ="<option value='' selected disabled>Select</option>";

        foreach($categories as $cat)
        {
            $categories_dropdown .= "<option value='".$cat->id."'>".$cat->name."</option>"; 
            $sub_categories = Category::where(['parent_id'=>$cat->id])->get();
            foreach($sub_categories as $sub_cat)
            {
                $categories_dropdown .= "<option value='".$sub_cat->id."'>&nbsp;--&nbsp; ".$sub_cat->name."</option>"; 
            }
        }
        return view('admin.products.add_product')->with(compact('categories_dropdown'));
    }

    public function viewProducts( Request $request){
        $products=Product::get();
        
        foreach($products as $key => $val){
            $category_name= Category::where(['id' => $val->category_id])->first();
            $products[$key]-> category_name = $category_name->name;
        }
        return view('admin.products.view_products')->with(compact('products'));
    }

    public function editProducts(Request $request, $id=null){
        
        if($request->isMethod('post')){
            $data = $request->all();

            
            if($request-> hasFile('image')){
                $image_temp = $request->file('image');
                if($image_temp-> isValid()){
                    $extension = $image_temp -> clientExtension();
                    $filename = rand(111, 99999).'.'.$extension;
                    $large_image_path = 'images/backend_images/products/large/'.$filename;
                    $medium_image_path = 'images/backend_images/products/medium/'.$filename;
                    $small_image_path = 'images/backend_images/products/small/'.$filename;
                    
                    Image::make($image_temp)->save($large_image_path);
                    Image::make($image_temp)->resize(600,600)->save($medium_image_path);
                    Image::make($image_temp)->resize(300,300)->save($small_image_path);
                    
                }

            }else{
                $filename = $data['current_image'];
            }

            if(empty($data['description'])){
                $product->description = 'Not specified';
            }

            Product::where(['id'=>$id])->update(['category_id'=>$data['category_id'],
            'product_name'=>$data['product_name'], 'product_code'=>$data['product_code'],
            'product_color'=>$data['product_color'], 'description'=>$data['description'],
            'price'=>$data['price'], 'image'=>$filename]);

            
            return redirect('/admin/view-products')->with('flash_message_success', 'Product updated successfully!!');

  
        }
        
        $productDetails = Product::where(['id'=>$id])->first();
         
        $categories = Category::where(['parent_id'=>0])->get();
        $categories_dropdown ="<option value='' selected disabled>Select</option>";

        foreach($categories as $cat)
        {
            if($cat->id == $productDetails->category_id)
            {
                $selected= "selected";
            }
            else{
                $selected= "";
            }
            $categories_dropdown .= "<option value='".$cat->id."' ".$selected ." >".$cat->name."</option>"; 
            $sub_categories = Category::where(['parent_id'=>$cat->id])->get();
            foreach($sub_categories as $sub_cat)
            {
                if($sub_cat->id == $productDetails->category_id)
                {
                    $selected= "selected";
                }
                else{
                    $selected= "";
                }
                $categories_dropdown .= "<option value='".$sub_cat->id."'".$selected .">&nbsp;--&nbsp; ".$sub_cat->name."</option>"; 
            }
        }
       
        return view('admin.products.edit_product')->with(compact('productDetails', 'categories_dropdown'));
    }

    public function deleteProduct($id=null){

        /* to delete image from folder
        $productImage = Product::where(['id'=> $id])->first();

        $small_image_path = 'images/backend_images/products/small';
        $medium_image_path = 'images/backend_images/products/medium';
        $large_image_path = 'images/backend_images/products/large';

        if(file_exists($small_image_path.$productImage->image)){
            unlink($small_image_path.$productImage->image);
        }*/

        Product::where(['id'=>$id])->delete();
        return redirect()->back()->with('flash_message_success', 'Product has been deleted!');

    }

    public function products($url=null){

        $countCategory = Category::where(['url'=>$url, 'status' => 1])->count();
        if($countCategory==0){
            abort(404);
        }

        $categories = Category::with(['categories'])->where(['parent_id'=>0])->get();
        $categoryDetails =Category::where(['url'=> $url])->first();

        if($categoryDetails->parent_id==0){
               // if the url in the sub category
            $subCategories =Category::where(['parent_id'=>$categoryDetails->id])->get();
            
            $cat_ids[]="";
            foreach( $subCategories as $subcat)
            {
                $cat_ids[] =$subcat->id;
            }
            $productsAll = Product::whereIn('category_id',$cat_ids)->get();
        }else{
            // if the url in the sub category
            $productsAll = Product::where(['category_id'=> $categoryDetails->id])->get();

        }

        return view ('products.listing')->with(compact('categories', 'categoryDetails', 'productsAll'));

    }
    public function product($id=null){

        $productDetails =Product::where('id', $id)->first();

        return view ('products.product')
    }
}
