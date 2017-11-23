<template>
    <div class="edit_patch_item">
        <component v-bind:is="editEntryComponent" :entry="oldEntry"></component>
        <table>
            <!-- <tr><td>Edit Headwords:</td><td><textarea v-model="headwordsTextarea"></textarea></td><td rowspan="2"><entry :entry="newEntry" :dict="dict"></entry></td></tr>
                 <tr><td>Edit Sense:</td><td><textarea v-model="senseTextarea"></textarea></td></tr> -->
            <tr><td>Comment:</td><td><textarea v-model="commentTextarea"></textarea></td></tr>
            <tr><td>Edit Flags:</td><td><input v-model="flagsInput"></input></td></tr>
            <tr><td>Set Approved:</td><td><input type="checkbox" v-model="approvedCheckbox"></input></td></tr>
            <tr><td>Set Merged in Upstream</td><td><input type="checkbox" v-model="mergedIntoTeiCheckbox"></input></td></tr>
            <a href="" @click.prevent="submitPatch">Submit Patch</a>
        </table>
    </div>
</template>

<script>
 import { mapState } from 'vuex';

 export default {
     props: ['previousItem'],

     data() {
         return { editEntryComponent: "", oldEntry: "", commentTextarea: "", flagsInput: "", approvedCheckbox: "", mergedIntoTeiCheckbox: "", dict: "", groupId: "foo"}; },

     methods: {

         submitPatch() {
             if (!$("#loggedIn").length) {
                 window.alert("You have to be logged in to submit patches!");
                 return
             }

             var data = {
                 dict:this.dict,
                 groupId:this.groupId,
                 newEntry:this.newEntry,
                 comment:this.commentTextarea,
                 flags:this.flagsInput,
                 approved:this.approvedCheckbox,
                 mergedIntoTei:this.mergedIntoTeiCheckbox,
                 keywords:this.newEntryKeywords,
             };
             axios.post('/submitPatch/',
                        data
             ).then(function (response) {
                 location.reload();
             });
         }
     },

     watch: {
         previousItem: function (val, oldval) {
             this.dict = this.previousItem.dict;
             if (this.previousItem.hasOwnProperty('entry')) {
                 this.oldEntry = this.previousItem.entry;
                 this.groupId = this.previousItem.entry_hash;
             } else {
                 this.oldEntry = this.previousItem.new_entry;
                 this.groupId = this.previousItem.group_id;
             }
             this.editEntryComponent=this.dict.replace("_","-")+"-edit-entry";

         }
     },

     computed: mapState([
         'newEntry',
         'newEntryKeywords'
     ])
 }
</script>
