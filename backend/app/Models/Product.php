<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Product extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'category_id',
        'brand_id',
        'sku',
        'barcode',
        'name',
        'slug',
        'description',
        'short_description',
        'unit_price',
        'pack_price',
        'carton_price',
        'pack_quantity',
        'carton_quantity',
        'cost_price',
        'wholesale_price',
        'retail_price',
        'currency',
        'unit',
        'weight',
        'dimensions',
        'stock_quantity',
        'low_stock_threshold',
        'is_active',
        'is_featured',
        'is_new',
        'is_bestseller',
        'is_on_sale',
        'sale_price',
        'sale_start_date',
        'sale_end_date',
        'tax_rate',
        'nutritional_info',
        'ingredients',
        'allergens',
        'manufacturer',
        'country_of_origin',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'views_count',
        'orders_count',
        'rating_average',
        'rating_count',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:3',
            'pack_price' => 'decimal:3',
            'carton_price' => 'decimal:3',
            'cost_price' => 'decimal:3',
            'wholesale_price' => 'decimal:3',
            'retail_price' => 'decimal:3',
            'sale_price' => 'decimal:3',
            'tax_rate' => 'decimal:2',
            'weight' => 'decimal:2',
            'stock_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
            'pack_quantity' => 'integer',
            'carton_quantity' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'is_bestseller' => 'boolean',
            'is_on_sale' => 'boolean',
            'sale_start_date' => 'datetime',
            'sale_end_date' => 'datetime',
            'dimensions' => 'array',
            'nutritional_info' => 'array',
            'ingredients' => 'array',
            'allergens' => 'array',
            'views_count' => 'integer',
            'orders_count' => 'integer',
            'rating_average' => 'decimal:2',
            'rating_count' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'sku', 'unit_price', 'stock_quantity', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relationships
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_products')->withTimestamps();
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOnSale($query)
    {
        return $query->where('is_on_sale', true)
            ->where(function ($q) {
                $q->whereNull('sale_start_date')
                    ->orWhere('sale_start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('sale_end_date')
                    ->orWhere('sale_end_date', '>=', now());
            });
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
    }

    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByBrand($query, $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
                ->orWhere('description', 'LIKE', "%{$term}%")
                ->orWhere('sku', 'LIKE', "%{$term}%")
                ->orWhere('barcode', 'LIKE', "%{$term}%");
        });
    }

    /**
     * Accessors
     */
    public function getCurrentPriceAttribute()
    {
        if ($this->is_on_sale && $this->sale_price) {
            $now = now();
            $saleActive = (!$this->sale_start_date || $this->sale_start_date <= $now)
                && (!$this->sale_end_date || $this->sale_end_date >= $now);

            if ($saleActive) {
                return $this->sale_price;
            }
        }

        return $this->unit_price;
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->is_on_sale && $this->sale_price && $this->unit_price > 0) {
            return round((($this->unit_price - $this->sale_price) / $this->unit_price) * 100, 2);
        }

        return 0;
    }

    public function getStockStatusAttribute()
    {
        if ($this->stock_quantity <= 0) {
            return 'out_of_stock';
        } elseif ($this->stock_quantity <= $this->low_stock_threshold) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    public function getIsInStockAttribute()
    {
        return $this->stock_quantity > 0;
    }

    public function getPrimaryImageAttribute()
    {
        return $this->images()->where('is_primary', true)->first()
            ?? $this->images()->first();
    }

    /**
     * Helper methods
     */
    public function decrementStock($quantity)
    {
        $this->decrement('stock_quantity', $quantity);

        if ($this->stock_quantity <= $this->low_stock_threshold) {
            // Trigger low stock notification
            event(new \App\Events\ProductLowStock($this));
        }
    }

    public function incrementStock($quantity)
    {
        $this->increment('stock_quantity', $quantity);
    }

    public function updateRating()
    {
        $this->rating_average = $this->reviews()->avg('rating');
        $this->rating_count = $this->reviews()->count();
        $this->save();
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function incrementOrders()
    {
        $this->increment('orders_count');
    }
}
