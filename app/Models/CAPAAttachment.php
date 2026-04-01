<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CAPAAttachment extends Model
{
    use HasFactory;

    protected $table = 'capa_attachments';

    public $timestamps = false;

    protected $fillable = [
        'capa_id',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'mime_type',
        'attachment_type',
        'description',
        'uploaded_by_user_id',
        'uploaded_at',
        'is_deleted',
        'deleted_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'uploaded_at' => 'datetime',
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function capa(): BelongsTo
    {
        return $this->belongsTo(CAPA::class, 'capa_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    /**
     * Scopes
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('attachment_type', $type);
    }

    /**
     * Accessors
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDownloadUrlAttribute(): string
    {
        return route('api.capa.attachments.download', $this->id);
    }

    /**
     * Get file icon based on file type
     */
    public function getFileIconAttribute(): string
    {
        $extension = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
        
        return match($extension) {
            'pdf' => 'document-text',
            'doc', 'docx' => 'document',
            'xls', 'xlsx' => 'table',
            'jpg', 'jpeg', 'png', 'gif' => 'photograph',
            'zip', 'rar' => 'archive',
            default => 'document',
        };
    }

    /**
     * Delete file from storage
     */
    public function deleteFile(): bool
    {
        if (Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }

        $this->update([
            'is_deleted' => true,
            'deleted_at' => now(),
        ]);

        return true;
    }
}
