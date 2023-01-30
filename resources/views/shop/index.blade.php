@extends('shop.layouts.main')
@section('title', 'S-Book | Trang chủ')
@section('content')

<div class="col-sm-9 padding-right">
	<div class="features_items"><!--features_items-->
		@if($recommend_products_id !=null)
			<h2 class="title text-center">Có thể bạn cũng thích</h2>
				@foreach ($recommend_products_id as $id)
					@foreach ($recommend_products as $product)
						@if($product->id == $id)
						<div class="col-sm-4">
							<a href="{{URL::to('/product-details/'.$product->id)}}">
							<div class="product-image-wrapper" style="height: 380px">
								<div class="single-products">
									<div class="productinfo text-center">
										<img src="{{asset("/assets/clients/pro_img/$product->image")}}" alt="" />
										<h2 style="margin-top: 5px">{{number_format($product->price, 0, '.',',').' vnđ'}}</h2>
										<form action="{{URL::to('/add-to-cart')}}" method="POST" style="margin-bottom:10px">
											@csrf
											<input type="hidden" value="{{Session::get('customer_id')}}" name="id"/>
											<input type="hidden" value="{{$product->id}}" name="id"/>
											<input type="hidden" value="1" name="count"/>
											<button type="submit" class="btn btn-default add-to-cart" >
												<i class="fa fa-shopping-cart"></i>
												Thêm vào giỏ hàng
											</button>
										</form>
										<h4 style="overflow: hidden; height:40px">{{$product->name}}</h4>
									</div>
								</div>
							</div>
							</a>
						</div>
					@endif
				@endforeach
			@endforeach
		@else
			<h2 class="title text-center">Một số sản phẩm hot</h2>
					@foreach ($recommend_products as $product)
						<div class="col-sm-4">
							<a href="{{URL::to('/product-details/'.$product->id)}}">
							<div class="product-image-wrapper" style="height: 380px">
								<div class="single-products">
									<div class="productinfo text-center">
										<img src="{{asset("/assets/clients/pro_img/$product->image")}}" alt="" />
										<h2 style="margin-top: 5px">{{number_format($product->price, 0, '.',',').' vnđ'}}</h2>
										<form action="{{URL::to('/add-to-cart')}}" method="POST" style="margin-bottom:10px">
											@csrf
											<input type="hidden" value="{{Session::get('customer_id')}}" name="id"/>
											<input type="hidden" value="{{$product->id}}" name="id"/>
											<input type="hidden" value="1" name="count"/>
											<button type="submit" class="btn btn-default add-to-cart" >
												<i class="fa fa-shopping-cart"></i>
												Thêm vào giỏ hàng
											</button>
										</form>
										<h4 style="overflow: hidden; height:40px">{{$product->name}}</h4>
									</div>
								</div>
							</div>
							</a>
						</div>
				@endforeach
		@endif
	</div><!--/category-tab-->
	<div class="features_items"><!--features_items-->
		<h2 class="title text-center">Tất cả sản phẩm</h2>
		@foreach ($all_product as $product)
		<div class="col-sm-4">
			<a href="{{URL::to('/product-details/'.$product->id)}}">
			<div class="product-image-wrapper" style="height: 380px">
				<div class="single-products">
					
					<div class="productinfo text-center">
						<img src="{{asset("/assets/clients/pro_img/$product->image")}}" alt="" />
						<h2 style="margin-top: 5px">{{number_format($product->price, 0, '.',',').' vnđ'}}</h2>
						<form action="{{URL::to('/add-to-cart')}}" method="POST" style="margin-bottom:10px">
							@csrf
							<input type="hidden" value="{{Session::get('customer_id')}}" name="id"/>
							<input type="hidden" value="{{$product->id}}" name="id"/>
							<input type="hidden" value="1" name="count"/>
							<button type="submit" class="btn btn-default add-to-cart" >
								<i class="fa fa-shopping-cart"></i>
								Thêm vào giỏ hàng
							</button>
						</form>
						<h4 style="overflow: hidden; height:40px">{{$product->name}}</h4>
					</div>
				</div>
			</div>
			</a>
		</div>
		@endforeach
	</div><!--/category-tab-->				
</div>
@endsection
