<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FrontController extends Controller
{
    //
    public function index()
    {
        $categories = Category::all();
        $brands = Brand::all();
        $latestProducts = Product::latest()->take(4)->get();
        $randomProducts = Product::inRandomOrder()->take(6)->get();

        return view('front.index', compact('categories', 'brands', 'latestProducts', 'randomProducts'));
    }

    public function category(Category $category)
    {
        session()->put('category_id', $category->id);

        return view('front.brands', compact('category'));
    }

    public function brand(Brand $brand)
    {
        $category_id = session()->get('category_id');

        $products = Product::where('brand_id', $brand->id)
            ->where('category_id', $category_id)
            ->latest()
            ->get();

        return view('front.gadgets', compact('brand', 'products'));
    }

    public function details(Product $product)
    {
        return view('front.details', compact('product'));
    }

    public function branding(Brand $brand)
    {
        $products = Product::where('brand_id', $brand->id)
            ->latest()
            ->get();

        return view('front.brandings', compact('brand', 'products'));
    }

    public function booking(Product $product)
    {
        $stores = Store::all();
        return view('front.booking', compact('product', 'stores'));
    }

    public function booking_save(StoreBookingRequest $request, Product $product)
{
    session()->put('product_id', $product->id);
    
    $bookingDate = $request->only(['duration', 'started_at', 'delivery_type', 'address', 'store_id']);
    session($bookingDate);
    
    return redirect()->route('front.checkout', $product->slug);
}

    public function checkout(Product $product)
    {
        // Debug all session data
        // dd(session()->all());
        $duration = session('duration');

        $insurance = 3000;
        $ppn = 0.11;
        $price = $product->price;

        $subTotal = $price * $duration;
        $totalPpn = $subTotal * $ppn;
        $grandTotal = $subTotal + $totalPpn + $insurance;
        
        return view('front.checkout', compact('product', 'subTotal', 'totalPpn', 'grandTotal', 'insurance'));
    }


    public function checkout_store(StorePaymentRequest $request){
        $bookingData = session()->only(['duration', 'started_at', 'store_id', 'delivery_type', 'address', 'product_id']);

        $duration = (int) $bookingData['duration'];
        $startedDate = Carbon::parse($bookingData['started_at']);

        $productDetails = Product::find($bookingData['product_id']);

        if(!$productDetails){
            return redirect()->back()->withErrors(['product_id' => 'Product not found.']);
        }


        $insurance = 3000;
        $ppn = 0.11;
        $price = $productDetails->price;


        $subTotal = $price * $duration;
        $totalPpn = $subTotal * $ppn;
        $grandTotal = $subTotal + $totalPpn + $insurance;
        
        
        $bookingTransactionId = null;

        DB::transaction(function() use ($request, &$bookingTransactionId, $duration, $bookingData, $grandTotal,
        $productDetails, $startedDate){

            $validated = $request->validated();

            if($request->hasFile('proof')){
                $proofPath = $request->file('proof')->store('proofs','public');
                $validated['proof'] = $proofPath;
            }

            $endedDate = $startedDate->copy()->addDays('duration');

            $validated['started_at'] = $startedDate;
            $validated['ended_at'] = $endedDate;
            $validated['duration'] = $duration;
            $validated['total_amount'] = $grandTotal;
            $validated['store_id'] = $bookingData['store_id'];
            $validated['product_id'] = $bookingData['product_id'];
            $validated['delivery_type'] = $bookingData['delivery_type'];
            $validated['address'] = $bookingData['address'];
            $validated['is_paid'] = false;
            $validated['trx_id'] = Transaction::generateUniqueTrxId();

            $newBooking = Transaction::create($validated);
            $bookingTransactionId = $newBooking->id;

        });

        return redirect()->route('front.success.booking', $bookingTransactionId);
    }

    public function success_booking(Transaction $transaction){
        return view('front.success_booking', compact('transaction'));
    }
    
    public function transactions(){
        return view('front.transactions');
    }
    
    public function transactions_details(Request $request){
        $request->validate([
            'trx_id' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:11']
        ]);
    
        $trx_id = $request->input('trx_id');
        $phone_number = $request->input('phone_number');
    
        $details = Transaction::with(['store', 'product'])
            ->where('trx_id', $trx_id)
            ->where('phone_number', $phone_number)
            ->first();
    
        if(!$details){
            return redirect()->back()->withErrors(['error' => "Transaction not found for trx_id: $trx_id and phone_number: $phone_number"]);
        }
    
        $insurance = 3000;
        $ppn = 0.11;
    
        $totalPpn = $details->product->price * $ppn;
        $subTotal = $details->product->price; // Removed duration from the calculation
        $grandTotal = $subTotal + $totalPpn + $insurance;
    
        return view('front.transaction_details', compact('details', 'totalPpn', 'subTotal', 'insurance','grandTotal'));
    }
  
}
