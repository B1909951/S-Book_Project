<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Session;
use App\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use App\Models\Product;
use App\Models\Genre;
use App\Models\Rate;
use App\Models\Category;
use App\Models\Product_genre;
use App\Models\Notification;
use App\Models\Customer;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
class ShopController extends Controller
{
    public function recommend_products(){

        //tạo file dữ liệu danh sách sản phẩm
        $all_product = Product::where('show','1')->get();
        $data = "Product_ID;Name;Info";
        $data .= "\r\n";
        foreach($all_product as $pro){
            $genres = Product_genre::where('product_id',$pro->id)->get();
            $data .=$pro->id;
            $data .=";";
            $data .=$pro->name;
            $data .=";";
            $data .=$pro->author;
            $data .="|";

            foreach($genres as $genre){
                $data .= $genre->name;
                $data .="|";
            }
            $cate_name = Category::where('id',$pro->cate_id)->first()->name;
            $data .= $cate_name;

            $data .= "\r\n";

        }
        Storage::disk('local')->put('python/products.txt', $data);

        //tạo file dữ liệu danh sách đánh giá của khách hàng hiện tại
        if(Session::get('customer_id')==null){
            return null;
        }
        $u_rating = Rate::where('customer_id',Session::get('customer_id'))->get();
        if(count($u_rating) == 0){
            return null;
        }
        $data = "Product_ID;Rating";
        $data .= "\r\n";
        
        foreach($u_rating as $rating){
            $data .= $rating->product_id;
            $data .=";";
            $data .= $rating->rating;
            $data .= "\r\n";
        }

        
        Storage::disk('local')->put('python/user_ratings.txt', $data);
        $process = new Process(["python3", 'D:\Programs\xampp2\htdocs\S-Book\storage\app\python\code.py'], env: [
            'SYSTEMROOT' => getenv('SYSTEMROOT'),
            'PATH' => getenv("PATH")
          ]);
          
        $process->run();
        $output_data = $process->getOutput();
        $arr = explode(" ",$output_data);
        foreach($arr as $key=>$value){
            $arr[$key] = (int)filter_var($value, FILTER_SANITIZE_NUMBER_INT);

        }
        $rec_pro_id_for_cus=array();
        for($i=0; $i<count($arr) && $i<6; $i++) {
            $rec_pro_id_for_cus[] = $arr[$i];
        }
        // echo "<pre>";
        // print_r($rec_pro_id_for_cus); 
        // echo "</pre>";
        return $rec_pro_id_for_cus;

    }
    public function index(){

        $notif = Notification::where('customer_id',Session::get('customer_id'))->orderby('notif_id','desc')->limit(6)->get();
        $recommend_products_id = $this->recommend_products();
        $recommend_products = null;
        if($recommend_products_id == null){
            $recommend_products = Product::join('categories','categories.id','=','products.cate_id')
            ->select('products.*','products.id as product_id')
            ->where('products.show','1')
            ->where('categories.show','1')
            ->orderby('view','desc')
            ->limit(6)
            ->get();
        }else{
            $recommend_products = Product::whereIn('products.id',$recommend_products_id)->get();
        }
        // return $recommend_products;
        // if(!Session::get('customer_id')){
        //     
        // }else{
        //     $recommend_products_arr = Product::join('categories','categories.id','=','products.cate_id')->select('products.*','products.id as product_id')->where('products.show','1')->where('categories.show','1')->orderby('view','desc')->limit(9)->get();
        // }
        $all_product = Product::join('categories','categories.id','=','products.cate_id')->select('products.*','products.id as product_id')->where('products.show','1')->where('categories.show','1')->orderby('product_id','desc')->get();
        $all_category = DB::table('categories')->where('categories.show','1')->get();
        $all_genre = DB::table('genres')->where('genres.show','1')->get();
        $product_genres = Product_genre::All();
        return view('shop/index')->with('all_product',$all_product)->with('product_genres',$product_genres)->with('all_genre',$all_genre)->with('all_category',$all_category)->with('notif',$notif)->with('recommend_products',$recommend_products)->with('recommend_products_id',$recommend_products_id);
    }
    public function search(request $request){
        $notif = Notification::where('customer_id',Session::get('customer_id'))->orderby('notif_id','desc')->limit(6)->get();

        $all_product = Product::where('products.show','1')->get();
        $all_category = DB::table('categories')->where('categories.show','1')->get();
        $all_genre = DB::table('genres')->where('genres.show','1')->get();
        $product_genres = Product_genre::All();
        $keywords = $request->keywords;
        $search_product = Product::join('categories','categories.id','=','products.cate_id')->select('products.*','products.id as product_id')->where('products.show','1')->where('categories.show','1')->where('products.name','like','%' .$keywords. '%')->select('products.id as pro_id', 'products.name', 'products.image', 'products.price')->orderby('pro_id','desc')->get();
        return view('shop/search')->with('all_product',$all_product)->with('product_genres',$product_genres)->with('all_genre',$all_genre)->with('all_category',$all_category)->with('search_product',$search_product)->with('keywords',$keywords)->with('notif',$notif);
    }
    public function product_details($id){
        $product = Product::find($id);
        if(isset($product)) {
            $product->view = $product->view + 1 ;
            $product->update();
        }
        $notif = Notification::where('customer_id',Session::get('customer_id'))->orderby('notif_id','desc')->limit(6)->get();

        $all_category = DB::table('categories')->where('categories.show','1')->get();
        $all_genre = DB::table('genres')->where('genres.show','1')->get();
        $pro_genre = DB::table('product_genres')->join('genres','genres.id','=','product_genres.genre_id')->where('genres.show','1')->where('product_genres.product_id',$id)->select('genres.name', 'product_genres.genre_id')->get();

        $comments = DB::table('rates')->where('rates.product_id',$id)->join('customers','customers.customer_id','=','rates.customer_id')->select('rates.*','customers.name','customers.customer_id')->orderby('rate_id','desc')->get();
        $same_products = DB::table('products')
        ->join('categories','categories.id','=','products.cate_id')
        ->where('categories.id',$product->cate_id)->where('products.show','1')->where('categories.show','1')->whereNotIn('products.id',[$id])->select('products.id as pro_id', 'products.name', 'products.image', 'products.price')->limit(6)->get();

        $exist_rate =  DB::table('rates')->where('rates.product_id',$id)->where('rates.customer_id',Session::get('customer_id'))->first();
        if(!$exist_rate) $exist_rate=null;
        $exist_order = DB::table('details_orders')->where('details_orders.product_id',$id)->where('details_orders.customer_id',Session::get('customer_id'))->first();
        if(!$exist_order) $exist_order=null;

        return view('shop/product_details')->with('product',$product)->with('all_genre',$all_genre)->with('same_products',$same_products)->with('all_category',$all_category)->with('pro_genre',$pro_genre)->with('comments',$comments)->with('exist_rate',$exist_rate)->with('exist_order',$exist_order)->with('notif',$notif);
    }
    public function cate_products($id){
        $notif = Notification::where('customer_id',Session::get('customer_id'))->orderby('notif_id','desc')->limit(6)->get();

        $cate_name = Category::find($id)->name;
        $all_category = DB::table('categories')->where('categories.show','1')->get();
        $all_genre = DB::table('genres')->where('genres.show','1')->get();
        $product_genres = Product_genre::All();
        $cate_products = DB::table('products')
        ->where('products.cate_id',$id)->where('products.show','1')->select('products.id as pro_id', 'products.*')->get();
        return view('shop/cate_products')->with('all_genre',$all_genre)->with('cate_products',$cate_products)->with('all_category',$all_category)->with('cate_name',$cate_name)->with('product_genres',$product_genres)->with('notif',$notif);
    }
    public function genre_products($id){
        $genre_name = Genre::find($id)->name;
        $notif = Notification::where('customer_id',Session::get('customer_id'))->orderby('notif_id','desc')->limit(6)->get();

        $all_category = DB::table('categories')->where('categories.show','1')->get();
        $all_genre = DB::table('genres')->where('genres.show','1')->get();
        $product_genres = Product_genre::All();
        $genre_products = DB::table('product_genres')->join('products','products.id','=','product_genres.product_id')
        ->where('product_genres.genre_id',$id)->where('products.show','1')->join('categories','categories.id','=','products.cate_id')->select('products.*','products.id as product_id')->where('categories.show','1')->select('products.id as pro_id', 'products.*')->get();
        return view('shop/genre_products')->with('all_genre',$all_genre)->with('genre_products',$genre_products)->with('all_category',$all_category)->with('genre_name',$genre_name)->with('product_genres',$product_genres)->with('notif',$notif);
    }
}
