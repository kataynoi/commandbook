<?php namespace App\Models;
use CodeIgniter\Model;

class CommandDocumentModel extends Model
{
    protected $table = 'command_documents';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'doc_number',
        'doc_title',
        'description',
        'doc_date',
        'file_name',
        'file_path',
        'file_size',
        'qr_token',
        'uploaded_by',
        'created_at'
    ];
}