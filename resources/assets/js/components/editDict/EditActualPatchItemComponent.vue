<template>
    <div class="edit_patch_item">
        <h1>Actual Entry</h1>
        <table border="1">
            <tr><td>Entry:</td><td><component v-bind:is="entryComponent" :entry="newEntry"></component></td></tr>
            <tr><td>Flags:</td>
                <td><input v-model="flagsInput" type="text" readonly></td>
            </tr>
            <tr >
                <td>Approved:</td>
                <td><input v-model="newApproved" type="checkbox" onclick="return false;"></td>
            </tr>
            <tr>
                <td>Merged into upstream:</td>
                <td><input v-model="newMergedIntoTei" type="checkbox" onclick="return false;"></td>
            </tr>
        </table>

        <br>
        <component v-bind:is="editEntryComponent" :entry="oldEntry"></component>
        <table>
            <div v-if="showEditFlagsBox">
                <fieldset >
                    <legend>Edit Flags</legend>
                    <div v-if="userRole == 'admin'">
                        <tr><td>Approved:</td><td><input type="checkbox" v-model="newApproved"></input></td></tr>
                        <tr><td>Merged in upstream</td><td><input type="checkbox" v-model="newMergedIntoTei"></input></td></tr>
                    </div>
                    <tr><td>Additional flags:</td><td><input v-model="flagsInput"></input></td></tr>
                    <a @click.preventDefault="cancelEditFlags()">Cancel</a>
                </fieldset>
            </div>
            <div v-else>
                <a @click.prevent="setShowEditFlags(true)">Edit Flags</a>
            </div>
            <br>
            <tr><td>Comment:</td><td><textarea v-model="newComment"></textarea></td></tr>
            <a v-if="!showEditFlagsBox && !showEditEntryBox" @click.prevent="submitPatch">Submit Comment</a>
            <a v-else @click.prevent="submitPatch">Submit Patch</a>
        </table>
    </div>
</template>

<script>

 import { mapState } from 'vuex';

 export default {
     props: ['previousItem'],

     data() {
         return { entryComponent: "", showEditFlagsBox: false, editEntryComponent: "",
                  oldEntry: "", newComment: "", oldFlags:"", flagsInput: "", oldApproved: false, newApproved: false, oldMergedIntoTei: false, newMergedIntoTei: false, groupId: ""}; },

     methods: {

         submitPatch() {
             if (!this.$store.state.userName) {
                 window.alert("You have to be logged in to submit patches or comments!");
                 return
             }
             if (!this.showEditEntryBox && !this.showEditFlagsBox && this.newComment == "") {
                 window.alert("Your comment is empty!");
                 return
             }
             if (this.showEditEntryBox && this.oldEntry == this.newEntry) {
                 window.alert("You didn't change the entry!");
                 return
             }
             if (this.showEditFlagsBox && this.oldFlags == this.flagsInput && this.oldApproved == this.newApproved && this.oldMergedIntoTei == this.newMergedIntoTei) {
                 window.alert("You didn't change the flags!");
                 return
             }

             var data = {
                 dict:this.selectedDict,
                 groupId:this.groupId,
                 newEntry:this.newEntry,
                 comment:this.newComment,
                 newFlags:this.flagsInput,
                 approved:this.newApproved,
                 mergedIntoTei:this.newMergedIntoTei,
                 keywords:this.newEntryKeywords,
             };
             axios.post('/submitPatch',
                        data
             ).then(function (response) {
                 location.reload();
             });
         },

         setShowEditFlags(boolean) {
             this.showEditFlagsBox = boolean;
         },

         cancelEditFlags() {
             if (this.previousItem.hasOwnProperty('new_flags')) {
                 this.flagsInput = this.previousItem.new_flags;
             }
             else {
                 this.flagsInput = "";
             }
             this.newMergedIntoTei = false;
             this.newApproved = false;
             this.showEditFlagsBox = false;
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
                 this.flagsInput = this.previousItem.new_flags;
                 this.oldFlags= this.previousItem.old_flags;
                 this.oldApproved = this.previousItem.approved;
                 this.meregdIntoTei = this.previousItem.merged_into_tei;
             }
             this.editEntryComponent=this.dict.replace("_","-")+"-edit-entry";
             this.entryComponent=this.selectedDict.replace("_","-")+"-show-entry";
         }
     },

     computed: mapState([
         'newEntry',
         'newEntryKeywords',
         'showEditEntryBox',
         'userRole',
         'selectedDict'
     ])
 }
</script>
