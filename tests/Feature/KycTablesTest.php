<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\KycDocumentStatus;
use App\Enums\KycUserType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KycTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_kyc_tables_have_expected_columns(): void
    {
        $typeColumns = ['id', 'user_type', 'name', 'is_required', 'sort_order', 'active', 'created_at', 'updated_at'];
        foreach ($typeColumns as $column) {
            $this->assertTrue(Schema::hasColumn('kyc_document_types', $column), "Missing kyc_document_types.{$column}");
        }

        $documentColumns = [
            'id', 'user_id', 'type_id', 'file_path', 'status',
            'reviewed_by', 'reviewed_at', 'rejection_reason', 'created_at', 'updated_at',
        ];
        foreach ($documentColumns as $column) {
            $this->assertTrue(Schema::hasColumn('kyc_documents', $column), "Missing kyc_documents.{$column}");
        }
    }

    public function test_kyc_enums_match_schema_registry(): void
    {
        $this->assertSame(['investor', 'agent'], array_column(KycUserType::cases(), 'value'));
        $this->assertSame(['pending', 'approved', 'rejected'], array_column(KycDocumentStatus::cases(), 'value'));
    }

    public function test_document_type_crud_uses_deactivation_not_hard_delete(): void
    {
        $id = DB::table('kyc_document_types')->insertGetId([
            'user_type' => KycUserType::Investor->value,
            'name' => 'National ID',
            'is_required' => true,
            'sort_order' => 1,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('National ID', DB::table('kyc_document_types')->where('id', $id)->value('name'));
        $this->assertSame(1, DB::table('kyc_document_types')->where('id', $id)->value('is_required'));

        DB::table('kyc_document_types')->where('id', $id)->update([
            'name' => 'Passport',
            'active' => false,
            'updated_at' => now(),
        ]);

        $this->assertSame('Passport', DB::table('kyc_document_types')->where('id', $id)->value('name'));
        $this->assertSame(0, DB::table('kyc_document_types')->where('id', $id)->value('active'));
        $this->assertSame(1, DB::table('kyc_document_types')->where('id', $id)->count());
    }

    public function test_document_status_flow_with_review_and_rejection_reason(): void
    {
        $reviewer = User::factory()->create();

        $typeId = DB::table('kyc_document_types')->insertGetId([
            'user_type' => KycUserType::Investor->value,
            'name' => 'National ID',
            'is_required' => true,
            'sort_order' => 1,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $documentId = DB::table('kyc_documents')->insertGetId([
            'user_id' => $reviewer->id,
            'type_id' => $typeId,
            'file_path' => 'kyc/investor/document-1.pdf',
            'status' => KycDocumentStatus::Pending->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('pending', DB::table('kyc_documents')->where('id', $documentId)->value('status'));

        DB::table('kyc_documents')->where('id', $documentId)->update([
            'status' => KycDocumentStatus::Approved->value,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('approved', DB::table('kyc_documents')->where('id', $documentId)->value('status'));
        $this->assertSame($reviewer->id, DB::table('kyc_documents')->where('id', $documentId)->value('reviewed_by'));

        DB::table('kyc_documents')->where('id', $documentId)->update([
            'status' => KycDocumentStatus::Rejected->value,
            'rejection_reason' => 'Blurry copy',
            'updated_at' => now(),
        ]);

        $this->assertSame('rejected', DB::table('kyc_documents')->where('id', $documentId)->value('status'));
        $this->assertSame('Blurry copy', DB::table('kyc_documents')->where('id', $documentId)->value('rejection_reason'));
    }

    public function test_kyc_tables_have_required_indexes(): void
    {
        $typeIndexes = collect(Schema::getIndexes('kyc_document_types'))->pluck('columns')->flatten()->all();
        $this->assertContains('user_type', $typeIndexes);
        $this->assertContains('active', $typeIndexes);

        $documentIndexes = collect(Schema::getIndexes('kyc_documents'))->pluck('columns')->flatten()->all();
        foreach (['user_id', 'type_id', 'status', 'reviewed_by', 'created_at'] as $column) {
            $this->assertContains($column, $documentIndexes);
        }
    }
}
