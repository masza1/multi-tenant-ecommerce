<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct()
    {
        // Only admins can access
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                abort(403, 'Unauthorized');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $products = Product::latest()->paginate(10);

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Products/Form', [
            'product' => null,
        ]);
    }

    public function store(Request $request)
    {
        try {
            \Log::debug('ProductController::store() - Request data', [
                'all' => $request->all(),
                'files' => $request->files->keys(),
            ]);

            $validated = $request->validate([
                'sku' => ['required', 'string', 'max:100', 'unique:products'],
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'price' => ['required', 'numeric', 'min:0'],
                'stock' => ['required', 'integer', 'min:0'],
                'image' => ['required', 'image', 'max:2048'],
                'is_active' => ['boolean'],
            ]);

            \Log::debug('ProductController::store() - Validation passed', ['validated' => $validated]);

            // Create product in transaction
            DB::transaction(function () use ($request, &$validated) {
                // Handle image upload
                if ($request->hasFile('image')) {
                    $validated['image_path'] = $request->file('image')->store('products', 'public');
                }

                Product::create($validated);
            });

            \Log::info('Product created successfully');

            return redirect()->route('admin.dashboard')
                ->with('success', __('messages.product_created_successfully'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::debug('ProductController::store() - Validation error', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('ProductController::store() - Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return back()->with('error', __('messages.product_creation_failed'))->withInput();
        }
    }

    public function edit(Product $product)
    {
        return Inertia::render('Admin/Products/Form', [
            'product' => $product,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        try {
            \Log::debug('ProductController::update() - Request data', [
                'product_id' => $product->id,
                'all' => $request->all(),
                'files' => $request->files->keys(),
            ]);

            // Check if trying to delete image without new image
            $hasNewImage = $request->hasFile('image');
            $willDeleteImage = $request->input('delete_image');
            $willHaveNoImage = $willDeleteImage && !$hasNewImage;

            $validated = $request->validate([
                'sku' => ['required', 'string', 'max:100', 'unique:products,sku,' . $product->id],
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'price' => ['required', 'numeric', 'min:0'],
                'stock' => ['required', 'integer', 'min:0'],
                'image' => ['nullable', 'image', 'max:2048'],
                'is_active' => ['boolean'],
            ]);

            // Validate that product will have at least one image
            if ($willHaveNoImage) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'image' => ['Product must have at least one image.'],
                ]);
            }

            \Log::debug('ProductController::update() - Validation passed', ['validated' => $validated]);

            // Update product in transaction
            DB::transaction(function () use ($request, $product, &$validated) {
                // Handle delete image
                if ($request->input('delete_image')) {
                    if ($product->image_path) {
                        Storage::disk('public')->delete($product->image_path);
                    }
                    $validated['image_path'] = null;
                }
                // Handle image upload
                elseif ($request->hasFile('image')) {
                    // Delete old image if exists
                    if ($product->image_path) {
                        Storage::disk('public')->delete($product->image_path);
                    }

                    $validated['image_path'] = $request->file('image')->store('products', 'public');
                }

                $product->update($validated);
            });

            \Log::info('Product updated successfully', ['product_id' => $product->id]);

            return redirect()->route('admin.dashboard')
                ->with('success', __('messages.product_updated_successfully'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::debug('ProductController::update() - Validation error', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('ProductController::update() - Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return back()->with('error', __('messages.product_update_failed'))->withInput();
        }
    }

    public function destroy(Product $product)
    {
        try {
            // Delete product in transaction
            DB::transaction(function () use ($product) {
                // Delete image
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }

                $product->delete();
            });

            return back()->with('success', __('messages.product_deleted_successfully'));
        } catch (\Exception $e) {
            return back()->with('error', __('messages.product_deletion_failed'));
        }
    }
}
