<?php

namespace App\Models;

use App\DocumentType;
use Database\Factories\ApplicantDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A supporting document, stored on the private disk and only served through an authorized route.
 *
 * @property int $id
 * @property int $user_id
 * @property DocumentType $type
 * @property string $path
 * @property string $original_name
 * @property string $mime_type
 * @property int $size
 */
#[Fillable(['type', 'path', 'original_name', 'mime_type', 'size'])]
class ApplicantDocument extends Model
{
    /** @use HasFactory<ApplicantDocumentFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'size' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
