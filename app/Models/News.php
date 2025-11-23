<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class News extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'cover_image',
        'content',
        'excerpt',
        'seo_title',
        'seo_description',
        'author_id',
        'status',
        'views',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'views' => 'integer',
    ];

    /**
     * Get the author of the news article
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Scope for published news
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    /**
     * Scope for draft news
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Generate slug from title
     */
    public static function generateSlug(string $title, ?int $id = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->when($id, fn($query) => $query->where('id', '!=', $id))->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get SEO title or fallback to regular title
     */
    public function getSeoTitleAttribute($value): string
    {
        return $value ?: $this->title;
    }

    /**
     * Get excerpt or generate from content
     */
    public function getExcerptAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        // Generate excerpt from content (strip HTML and limit to 150 chars)
        $plainText = strip_tags($this->content);
        return Str::limit($plainText, 150);
    }

    /**
     * Get cover image URL with fallback
     */
    public function getCoverImageUrlAttribute(): string
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }

        // Fallback to Unsplash placeholder
        return "https://picsum.photos/800/400?random=" . $this->id;
    }

    /**
     * Check if the news is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && 
               $this->published_at && 
               $this->published_at <= now();
    }

    /**
     * Increment views count
     */
    public function incrementViews(): void
    {
        $this->increment('views');
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($news) {
            if (empty($news->slug)) {
                $news->slug = static::generateSlug($news->title);
            }
            if ($news->status === 'published' && !$news->published_at) {
                $news->published_at = now();
            }
        });

        static::updating(function ($news) {
            if ($news->isDirty('title') && empty($news->slug)) {
                $news->slug = static::generateSlug($news->title, $news->id);
            }
            if ($news->status === 'published' && !$news->published_at) {
                $news->published_at = now();
            }
        });
    }
}
