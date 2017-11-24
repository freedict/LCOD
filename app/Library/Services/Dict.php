<?php

namespace App\Library\Services;

use Illuminate\Support\Facades\DB;
use Log;

class Dict
{

    public function __construct()
    {
//         // get List of dicts
//         $this->dicts = DB::select("SELECT * FROM pg_catalog.pg_tables WHERE schemaname != 'pg_catalog' AND
// schemaname != 'information_schema';");
//         $this->dicts = array_map(function ($x){
//             return $x->tablename;
//         }, $this->dicts);
//         $this->dicts = array_filter($this->dicts, function ($x){
//             return (strpos($x, "index") == false) && (strpos($x, "patch") == false);
//         });

        // only for development phase
        $this->dicts = array("afr_deu",
"afr_eng",
"ara_eng",
"bre_fra",
"ces_eng",
"ckb_kmr",
"cym_eng",
"dan_eng",
"deu_eng",
"deu_fra",
"deu_ita",
"deu_kur",
"deu_nld",
"deu_por",
"deu_swe",
"deu_tur",
"eng_afr",
"eng_ara",
"eng_ces",
"eng_cym",
"eng_deu",
"eng_ell",
"eng_fra",
"eng_gle",
"eng_hin",
"eng_hrv",
"eng_hun",
"eng_ita",
"eng_lat",
"eng_lit",
"eng_nld",
"eng_pol",
"eng_por",
"eng_rom",
"eng_rus",
"eng_spa",
"eng_srp",
"eng_swe",
"eng_swh",
"eng_tur",
"epo_eng",
"fra_bre",
"fra_deu",
"fra_eng",
"fra_nld",
"gla_deu",
"gle_eng",
"gle_pol",
"hrv_eng",
"hun_eng",
"isl_eng",
"ita_deu",
"ita_eng",
"jpn_deu",
"jpn_eng",
"jpn_fra",
"jpn_rus",
"kha_deu",
"kha_eng",
"kur_deu",
"kur_eng",
"kur_tur",
"lat_deu",
"lat_eng",
"lit_eng",
"mkd_bul",
"nld_deu",
"nld_eng",
"nld_fra",
"nno_nob",
"oci_cat",
"pol_gle",
"por_deu",
"por_eng",
"san_deu",
"slk_eng",
"spa_ast",
"spa_deu",
"spa_eng",
"spa_por",
"srp_eng",
"swe_deu",
"swe_eng",
"swh_eng",
"swh_pol",
"tur_deu",
"tur_eng");
    }

    public function suggestion(string $partOfKeyword, string $dict)
    {
        $teiTable = 'tei_'.$dict;
        $teiTableIndex = $teiTable.'_index';
        $patchesTable = 'patches_'.$dict;
        $patchesTableIndex = $patchesTable.'_index';

        // Lookup the newest patches with keywords like $partOfKeyword%, which are approved
        // and not merged into tei and in addtion return only one patch per
        // group.
        $sqlCmd = "
SELECT DISTINCT ON ($patchesTable.group_id) *
FROM $patchesTable
JOIN  $patchesTableIndex ON $patchesTableIndex.patch_id = $patchesTable.id
WHERE $patchesTableIndex.keyword LIKE ?
AND $patchesTable.approved = true
AND $patchesTable.merged_into_tei = false
ORDER BY $patchesTable.group_id, $patchesTable.id DESC;
";
        $patchesLookupResult = DB::select($sqlCmd, [$partOfKeyword.'%']);

        // Lookup tei table entries. Exclude entries with patches, which are not approved and not merged into tei.
        $teiTable = 'tei_'.$dict;
        $teiTableIndex = $teiTable.'_index';
        $groupIdsOfPatches = array_map(function ($row) { return $row->group_id;}, $patchesLookupResult);
        $teiLookupResult = DB::table($teiTable)
                          ->join($teiTableIndex, $teiTable.'.entry_hash','=', $teiTableIndex.'.entry_hash')
                          ->select($teiTable.'.*', $teiTableIndex.'.*')
                          ->whereRaw($teiTableIndex.'.keyword like ?', [$partOfKeyword.'%'])
                          ->whereNotIn($teiTable.'.entry_hash', $groupIdsOfPatches)
                          ->get()
                          ->toArray();

        $result = array_merge($patchesLookupResult, $teiLookupResult);
        usort($result, function ($a, $b) {
            return strcmp($a->keyword, $b->keyword);
        });
        $result = json_encode($result, JSON_UNESCAPED_UNICODE);
        return $result;
    }

    public function lookup(string $keyword, string $dict)
    {
        $patchesTable = 'patches_' . $dict;
        $patchesTableIndex = $patchesTable . '_index';
        $teiTable = 'tei_'.$dict;
        $teiTableIndex = $teiTable.'_index';

        // Lookup the newest patches matching keyword, which are approved and
        // not merged into tei and in addtion return only one patch per
        // group.
        $sqlCmd = "
SELECT DISTINCT ON ($patchesTable.group_id) * , '$dict' as dict
FROM $patchesTable
JOIN  $patchesTableIndex ON $patchesTableIndex.patch_id = $patchesTable.id
WHERE $patchesTableIndex.keyword = ?
AND $patchesTable.merged_into_tei = false
AND $patchesTable.approved = true
ORDER BY $patchesTable.group_id, $patchesTable.id DESC;
";
        $patchesLookupResult = DB::select($sqlCmd, [$keyword]);

        // Lookup tei table entries. Exclude entries with approved patches.
        $groupIdsOfPatches = array_map(function ($row) { return $row->group_id;}, $patchesLookupResult);
        $teiLookupResult = DB::table($teiTable)
                         ->join($teiTableIndex, $teiTable.'.entry_hash','=', $teiTableIndex.'.entry_hash')
                         ->select(DB::raw("$teiTable.*, $teiTableIndex.*, '$dict' as dict" ))
                         ->where($teiTableIndex.'.keyword', '=', $keyword)
                         ->whereNotIn($teiTable.'.entry_hash', $groupIdsOfPatches)
                         ->get()
                         ->toArray();

        $result = array_merge($patchesLookupResult, $teiLookupResult);
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function submitPatch($dict, int $userId, string $groupId, $optArgs = array())
        // string $dict, array $keywords, int $userId, string $groupId, string $newEntry, string $comment, string $newFlags, bool $newApproved, bool $newMergedIntoTei)
    {
        $newEntry = !isset($optArgs['newEntry']) ? '' : $optArgs['newEntry'];
        $comment = !isset($optArgs['comment']) ? '' : $optArgs['comment'];
        $newFlags = !isset($optArgs['newFlags']) ? '' : $optArgs['newFlags'];
        $approved = !isset($optArgs['approved']) ? false : $optArgs['approved'];
        $mergedIntoTei = !isset($optArgs['mergedIntoTei']) ? false : $optArgs['mergedIntoTei'];
        $keywords= !isset($optArgs['keywords']) ? [] : $optArgs['keywords'];

        Log::Info($optArgs);

        $patchesTable = 'patches_'.$dict;
        $patchesTableIndex = $patchesTable.'_index';
        $teiTable = 'tei_'.$dict;

        // calculate oldEntry
        $lastPatchNewEntry = DB::select("SELECT * FROM $patchesTable WHERE group_id=? ORDER BY id DESC LIMIT 1;", [$groupId]);
        if (sizeof($lastPatchNewEntry) > 0) {
            $oldEntry = $lastPatchNewEntry[0]->new_entry;
            $oldFlags = $lastPatchNewEntry[0]->new_flags;
        }
        else {
            $oldEntry = DB::select("SELECT entry FROM $teiTable WHERE entry_hash=?;", [$groupId])[0]->entry;
            $oldFlags =  "";
        }
        // insert patch
        $sqlCmd = "
INSERT INTO $patchesTable (user_id, group_id, old_entry, new_entry, comment, old_flags, new_flags, approved, merged_into_tei)
VALUES (:user_id, :group_id, :old_entry, :new_entry, :comment, :old_flags, :new_flags, :approved, :merged_into_tei)
RETURNING id;
";
        $strMap = ['user_id' => $userId, 'group_id' => $groupId, 'old_entry' => $oldEntry,
                   'new_entry' => $newEntry, 'comment' => $comment, 'old_flags' => $oldFlags,
                   'new_flags' => $newFlags, 'approved' => $approved, 'merged_into_tei' => $mergedIntoTei];
        $patchId = DB::select($sqlCmd, $strMap)[0]->id;
        // insert patch index
        foreach ($keywords as $keyword) {
            $sqlCmd = "INSERT INTO $patchesTableIndex VALUES (:keyword, unaccent( :keyword_ ), :patchId)";
            DB::insert($sqlCmd, ['keyword' => $keyword, 'keyword_' => $keyword ,'patchId' => $patchId]);
        }
        return $patchId;
    }

    public function submitPatchUpdate(string $dict, int $patchId, bool $approved, bool $mergedIntoTei)
    {
        $patchesTable = 'patches_'.$dict;
        $patchesTableIndex = $patchesTable.'_index';
        $teiTable = 'tei_'.$dict;

        // insert patch
        DB::table($patchesTable)
            ->where('id', $patchId)
            ->update(['approved' => $approved, 'merged_into_tei' => $mergedIntoTei]);
    }

    public function lookupPatchGroup(string $dict, string $groupId)
    {
        $patchTable = 'patches_'.$dict;
        $teiTable = "tei_".$dict;

        $patchTableResult = DB::table($patchTable)
                          ->select(DB::raw("*, '$dict' as dict" ))
                          ->where('group_id', '=', $groupId)
                          ->join('users', 'users.id', '=', $patchTable.'.user_id')
                          ->select(DB::raw("$patchTable.*, '$dict' as dict, users.name as user_name"))
                          ->orderBy($patchTable.'.id', 'desc')
                          ->get()
                          ->toArray();
        $teiTableResult = DB::table($teiTable)
                        ->select(DB::raw("*, '$dict' as dict" ))
                        ->where('entry_hash', '=', $groupId)
                        ->get()
                        ->toArray();
        $result = array_merge($patchTableResult, $teiTableResult);
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function getAllDictNames()
    {
        return json_encode($this->dicts);
    }

    public function validateDict(string $dict)
    {
        if (!in_array($dict, $this->dicts)) {
            abort(400, 'Unvalid Dictionary selected.');
        }
        return true;
    }
    public function validateGroupIdAndDict(string $dict, string $groupId)
    {
        $this->validateDict($dict);
        if (sizeof(json_decode($this->lookupPatchGroup($dict, $groupId))) == 0)
            abort(400, 'Unvalid GroupId selected.');
        return true;
    }

}