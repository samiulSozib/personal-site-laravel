<?php

namespace App\Support;

/**
 * Allowed values for the content types. The public site and the admin forms both read these,
 * so a new cover or block type only needs to be added here and in the renderer.
 */
final class Content
{
    public const CATEGORIES = ['ai', 'coding', 'engineering'];

    public const CATEGORY_LABELS = ['ai' => 'AI & ML', 'coding' => 'Coding', 'engineering' => 'Engineering'];

    public const COVERS = ['neural', 'circuit', 'code', 'data', 'terminal', 'orbit'];

    public const PROJECT_STATUSES = ['live', 'in progress', 'archived'];

    public const EMPLOYMENT_TYPES = ['full-time', 'part-time', 'contract', 'freelance', 'internship'];

    public const BLOCK_TYPES = ['paragraph', 'heading', 'quote', 'list', 'code', 'callout'];

    public const LANGUAGES = ['en', 'bn'];
}
