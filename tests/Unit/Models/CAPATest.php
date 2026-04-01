<?php

namespace Tests\Unit\Models;

use App\Models\CAPA;
use App\Models\NCR;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CAPATest extends TestCase
{
    use RefreshDatabase;

    public function test_capa_can_be_created()
    {
        $capa = CAPA::factory()->create([
            'capa_number' => 'CAPA-TEST-001',
            'current_status' => 'Draft',
        ]);

        $this->assertDatabaseHas('capas', [
            'capa_number' => 'CAPA-TEST-001',
            'current_status' => 'Draft',
        ]);
    }

    public function test_capa_belongs_to_ncr()
    {
        $ncr = NCR::factory()->create();
        $capa = CAPA::factory()->create(['ncr_id' => $ncr->id]);

        $this->assertInstanceOf(NCR::class, $capa->ncr);
        $this->assertEquals($ncr->id, $capa->ncr->id);
    }

    public function test_capa_belongs_to_assigned_pic()
    {
        $user = User::factory()->create();
        $capa = CAPA::factory()->create(['assigned_pic_id' => $user->id]);

        $this->assertInstanceOf(User::class, $capa->assignedPic);
        $this->assertEquals($user->id, $capa->assignedPic->id);
    }

    public function test_capa_belongs_to_assigned_by()
    {
        $user = User::factory()->create();
        $capa = CAPA::factory()->create(['assigned_by_user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $capa->assignedBy);
        $this->assertEquals($user->id, $capa->assignedBy->id);
    }

    public function test_capa_belongs_to_verified_by()
    {
        $user = User::factory()->create();
        $capa = CAPA::factory()->create(['verified_by_user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $capa->verifiedBy);
        $this->assertEquals($user->id, $capa->verifiedBy->id);
    }

    public function test_capa_belongs_to_closed_by()
    {
        $user = User::factory()->create();
        $capa = CAPA::factory()->create(['closed_by_user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $capa->closedBy);
        $this->assertEquals($user->id, $capa->closedBy->id);
    }

    public function test_scope_by_status()
    {
        CAPA::factory()->create(['current_status' => 'Draft']);
        CAPA::factory()->create(['current_status' => 'In_Progress']);
        CAPA::factory()->create(['current_status' => 'Draft']);

        $this->assertEquals(2, CAPA::byStatus('Draft')->count());
        $this->assertEquals(1, CAPA::byStatus('In_Progress')->count());
    }

    public function test_scope_assigned_to_pic()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        
        CAPA::factory()->create(['assigned_pic_id' => $user->id]);
        CAPA::factory()->create(['assigned_pic_id' => $user->id]);
        CAPA::factory()->create(['assigned_pic_id' => $otherUser->id]);

        $this->assertEquals(2, CAPA::assignedToPic($user->id)->count());
        $this->assertEquals(1, CAPA::assignedToPic($otherUser->id)->count());
    }
}
