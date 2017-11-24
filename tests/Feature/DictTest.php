<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class DictTest extends TestCase
{
    public function __construct()
    {
        parent::__construct();

        $this->dict = "test_dict";
        $this->userId = 1;
    }

    public function setUp()
    {
        parent::setUp();

        Schema::create('tei_'.$this->dict, function (Blueprint $table) {
            $table->string('entry_hash');
            $table->string('entry');

        });
        Schema::create('tei_'.$this->dict.'_index', function (Blueprint $table) {
            $table->string('keyword');
            $table->string('keyword_unaccent');
            $table->string('entry_hash');
        });
        Schema::create('patches_'.$this->dict, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('group_id');
            $table->string('old_entry');
            $table->string('new_entry');
            $table->string('comment');
            $table->string('old_flags');
            $table->string('new_flags');
            $table->boolean('approved');
            $table->boolean('merged_into_tei');
            $table->integer('created_date')->nullable();
        });
        Schema::create('patches_'.$this->dict.'_index', function (Blueprint $table) {
            $table->string('keyword');
            $table->string('keyword_unaccent');
            $table->integer('patch_id');
        });
    }

    public function tearDown()
    {
        Schema::dropIfExists('tei_'.$this->dict);
        Schema::dropIfExists('tei_'.$this->dict.'_index');
        Schema::dropIfExists('patches_'.$this->dict);
        Schema::dropIfExists('patches_'.$this->dict.'_index');

        parent::tearDown();
    }

    private function createTeiTableEntryWithIndex($entry, $keywords)
    {
        $teiTable = 'tei_'.$this->dict;
        $teiTableIndex = $teiTable.'_index';

        $sqlCmd = "
INSERT INTO $teiTable
VALUES (md5(:entry), :entry);
";
        DB::insert($sqlCmd, ['entry' => $entry, 'entry' => $entry]);

        // create index
        foreach ($keywords as $keyword) {
            $sqlCmd = "
INSERT INTO $teiTableIndex
VALUES (:keyword, unaccent(:keyword_), md5(:entry));
";
            DB::insert($sqlCmd, ['keyword' => $keyword, 'keyword_' => $keyword, 'entry' => $entry ]);
        }
    }

    public function testLookup()
    {
        $dictLib = resolve('App\Library\Services\Dict');
        $this->createTeiTableEntryWithIndex('tei entry without a patch', ['â key word']);
        $entry = 'tei entry with multiple patches';
        $this->createTeiTableEntryWithIndex($entry, ['â key word']);
        $groupId = md5($entry);
        $dictLib->submitPatch($this->dict, $this->userId, $groupId, array('keywords' => ['â key word'], 'approved' => true, 'newEntry' => 'entry patched 1'));
        $dictLib->submitPatch($this->dict, $this->userId, $groupId, array('keywords' => ['â key word'], 'approved' => true, 'newEntry' => 'entry patched 2'));
        $dictLib->submitPatch($this->dict, $this->userId, $groupId, array('keywords' => ['â key word'], 'approved' => false, 'newEntry' => 'entry patched 3'));
        $dictLib->submitPatch($this->dict, $this->userId, $groupId, array('keywords' => ['â key word'], 'approved' => true, 'newEntry' => 'entry patched 4', 'mergedIntoTei' => true));

        $result = json_decode($dictLib->lookup('â key word', $this->dict));

        //print_r($result);
        $this->assertEquals($result[0]->new_entry, 'entry patched 2');
        $this->assertEquals($result[1]->entry, 'tei entry without a patch');
    }

    public function testSuggestion()
    {
        $dictLib = resolve('App\Library\Services\Dict');
        // an tei entry without an patch
        $this->createTeiTableEntryWithIndex('tei entry without a patch', ['â key word 0']);
        // an tei entry with multiple patches. Some are approved and merged in tei - some not.
        $entry = 'tei entry with multiple patches';
        $this->createTeiTableEntryWithIndex($entry, ['â key word 1']);
        $groupId = md5($entry);
        $dictLib->submitPatch($this->dict, $this->userId, $groupId, array('keywords' => ['â key word 1'], 'approved' => true, 'newEntry' => 'entry patched 1'));
        $dictLib->submitPatch($this->dict, $this->userId, $groupId, array('keywords' => ['â key word 2 which is very long'], 'approved' => true, 'newEntry' => 'entry patched 2'));
        $dictLib->submitPatch($this->dict, $this->userId, $groupId, array('keywords' => ['â key word 3'], 'approved' => false, 'newEntry' => 'entry patched 3'));
        $dictLib->submitPatch($this->dict, $this->userId, $groupId, array('keywords' => ['â key word 4'], 'approved' => true, 'newEntry' => 'entry patched 4', 'mergedIntoTei' => true));

        $result = json_decode($dictLib->suggestion('â key', $this->dict));

        //print_r($result);
        $this->assertEquals($result[1]->new_entry, 'entry patched 2');
        $this->assertEquals($result[0]->entry, 'tei entry without a patch');
    }

    public function testSubmitPatch()
    {
        $dictLib = resolve('App\Library\Services\Dict');
        $this->createTeiTableEntryWithIndex('tei entry', ['â key word']);
        $groupId =md5('tei entry');

        $dictLib->submitPatch($this->dict, $this->userId, $groupId, array('keywords' => ['keyword'], 'newEntry' => 'tei entry patched', 'approved' => true));
        $dictLib->submitPatch($this->dict, $this->userId, $groupId, array('keywords' => ['keyword'], 'newEntry' => 'tei entry patched2', 'approved' => true));
        $dictLib->submitPatch($this->dict, $this->userId, $groupId, array('keywords' => ['keyword'], 'newEntry' => 'tei entry patched3', 'approved' => true, 'mergedIntoTei' => true));

        $result = json_decode($dictLib->lookup('keyword', $this->dict));
        //print_r($result);
        $this->assertEquals($result[0]->old_entry, 'tei entry patched');
        $this->assertEquals($result[0]->new_entry, 'tei entry patched2');
    }

    public function testSubmitPatchUpdate()
    {
        $this->createTeiTableEntryWithIndex('tei entry', ['â key word']);
        $dictLib = resolve('App\Library\Services\Dict');
        $groupId = md5('tei entry');
        $patchId = $dictLib->submitPatch($this->dict, $this->userId, $groupId, array('mergedIntoTei' => true, 'keywords' => ['â key word']));
        $dictLib->submitPatchUpdate($this->dict, $patchId, $approved = true, $mergedIntoTei = false);

        $result = json_decode($dictLib->lookup('â key word', $this->dict));

        //print_r($result);
        $this->assertEquals($result[0]->approved, true);
        $this->assertEquals($result[0]->merged_into_tei, false);
    }

    public function testLookupPatchGroup()
    {
        $this->createTeiTableEntryWithIndex('tei entry', ['â key word']);
        $dictLib = resolve('App\Library\Services\Dict');
        $groupId = md5('tei entry');
        $dictLib->submitPatch($this->dict, $this->userId, $groupId, array('mergedIntoTei' => true, 'keywords' => ['â key word']));
        $dictLib->submitPatch($this->dict, $this->userId, $groupId, array('mergedIntoTei' => true, 'keywords' => ['â key word']));

        $result = json_decode($dictLib->lookupPatchGroup($this->dict, $groupId));

        //print_r($result);
        $this->assertTrue(sizeof($result) == 3);
    }

}
