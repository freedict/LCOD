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
            $table->string('flags');
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

    private function createPatchTableEntryWithIndex($groupId, $newEntry, $keywords, $args = array())
    {
        $oldEntry = !isset($args['oldEntry']) ? '' : $args['oldEntry'];
        $userId = !isset($args['userId']) ? 1 : $args['userId'];
        $comment = !isset($args['comment']) ? '' : $args['comment'];
        $flags = !isset($args['flags']) ? '' : $args['flags'];
        $approved = !isset($args['approved']) ? true : $args['approved'];
        $mergedIntoTei = !isset($args['mergedIntoTei']) ? false : $args['mergedIntoTei'];

        $createdDate = time();
        $patchesTable = 'patches_'.$this->dict;
        $patchesTableIndex = $patchesTable.'_index';

        // insert patch
        $sqlCmd = "
INSERT INTO $patchesTable (user_id, group_id, old_entry, new_entry, comment, flags, approved, merged_into_tei, created_date)
VALUES (:user_id, :group_id, :old_entry, :new_entry, :comment, :flags, :approved, :merged_into_tei, :created_date)
RETURNING id;
";
        $strMap = ['user_id' => $userId, 'group_id' => $groupId, 'old_entry' => $oldEntry, 'new_entry' => $newEntry, 'comment' => $comment, 'flags' => $flags, 'approved' => $approved, 'merged_into_tei' => $mergedIntoTei, 'created_date' => $createdDate];
        $patchId = DB::select($sqlCmd, $strMap)[0]->id;

        // insert patch index
        foreach ($keywords as $keyword) {
            $sqlCmd = "INSERT INTO $patchesTableIndex VALUES (:keyword, unaccent( :keyword_ ), :patchId)";
            DB::insert($sqlCmd, ['keyword' => $keyword, 'keyword_' => $keyword,'patchId' => $patchId]);
        }
    }

    public function testLookup()
    {
        // an tei entry without an patch
        $this->createTeiTableEntryWithIndex('tei entry without a patch', ['â key word']);
        // an tei entry with multiple patches. Some are approved and merged in tei - some not.
        $entry = 'tei entry with multiple patches';
        $this->createTeiTableEntryWithIndex($entry, ['â key word']);
        $groupId = md5($entry);
        $this->createPatchTableEntryWithIndex($groupId, $newEntry = 'entry patched 1', $keywords = ['â key word'], array('approved' => true));
        $this->createPatchTableEntryWithIndex($groupId, $newEntry = 'entry patched 2', $keywords = ['â key word'], array('approved' => true));
        $this->createPatchTableEntryWithIndex($groupId, $newEntry = 'entry patched 3', $keywords = ['â key word'], array('approved' => false));
        $this->createPatchTableEntryWithIndex($groupId, $newEntry = 'entry patched 4', $keywords = ['â key word'], array('approved' => true, 'mergedIntoTei' => true));

        $dictLib = resolve('App\Library\Services\Dict');
        $result = json_decode($dictLib->lookup('â key word', $this->dict));
        //print_r($result);
        $this->assertEquals($result[0]->new_entry, 'entry patched 2');
        $this->assertEquals($result[1]->entry, 'tei entry without a patch');
    }

    public function testSuggestion()
    {
        // an tei entry without an patch
        $this->createTeiTableEntryWithIndex('tei entry without a patch', ['â key word 0']);
        // an tei entry with multiple patches. Some are approved and merged in tei - some not.
        $entry = 'tei entry with multiple patches';
        $this->createTeiTableEntryWithIndex($entry, ['â key word 1']);
        $groupId = md5($entry);
        $this->createPatchTableEntryWithIndex($groupId, $newEntry = 'entry patched 1', $keywords = ['â key word 2'], array('approved' => true));
        $this->createPatchTableEntryWithIndex($groupId, $newEntry = 'entry patched 2', $keywords = ['â key word 3 which is very long'], array('approved' => true));
        $this->createPatchTableEntryWithIndex($groupId, $newEntry = 'entry patched 3', $keywords = ['â key word 4'], array('approved' => false));
        $this->createPatchTableEntryWithIndex($groupId, $newEntry = 'entry patched 4', $keywords = ['â key word 5'], array('approved' => true, 'mergedIntoTei' => true));

        $dictLib = resolve('App\Library\Services\Dict');
        $result = json_decode($dictLib->suggestion('â key', $this->dict));
        //print_r($result);
        $this->assertEquals($result[1]->new_entry, 'entry patched 2');
        $this->assertEquals($result[0]->entry, 'tei entry without a patch');
    }

    public function testSubmitPatch()
    {
        $this->createTeiTableEntryWithIndex('tei entry', ['â key word']);

        $dictLib = resolve('App\Library\Services\Dict');
        $dictLib->submitPatch($this->dict, $keywords = ['keyword'], $userId = 1,  $groupId = md5('tei entry'),
                           $newEntry = 'tei entry patched',  $comment = '',  $flags = '',  $approved = true,  $mergedIntoTei = false);
        $dictLib->submitPatch($this->dict, $keywords = ['keyword'], $userId = 1,  $groupId = md5('tei entry'),
                           $newEntry = 'tei entry patched2',  $comment = '',  $flags = '',  $approved = true,  $mergedIntoTei = false);

        $result = json_decode($dictLib->lookup('keyword', $this->dict));
        //print_r($result);
        $this->assertEquals($result[0]->old_entry, 'tei entry patched');
        $this->assertEquals($result[0]->new_entry, 'tei entry patched2');
    }

    public function testLookupPatchGroup()
    {
        $this->createTeiTableEntryWithIndex('tei entry', ['â key word']);
        $dictLib = resolve('App\Library\Services\Dict');
        $groupId = md5('tei entry');
        $dictLib->submitPatch($this->dict, $keywords = ['keyword'], $userId = 1,  $groupId,
                              $newEntry = 'tei entry patched',  $comment = '',  $flags = '',  $approved = true,  $mergedIntoTei = false);
        $dictLib->submitPatch($this->dict, $keywords = ['keyword'], $userId = 1,  $groupId,
                              $newEntry = 'tei entry patched2',  $comment = '',  $flags = '',  $approved = true,  $mergedIntoTei = false);

        $result = json_decode($dictLib->lookupPatchGroup($this->dict, $groupId));
        //print_r($result);
        $this->assertTrue(sizeof($result) == 3);
    }

}
