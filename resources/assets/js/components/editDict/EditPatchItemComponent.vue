<template>
    <div class="edit_patch_item">
        <component v-bind:is="editEntryComponent" :entry="oldEntry"></component>
        <br>
        <table>
            <div v-if="showEditFlagsBox">
                <fieldset >
                    <legend>Edit Flags</legend>
                    <div v-if="userRole == 'admin'">
                        <tr><td>Approved:</td><td><input type="checkbox" v-model="approvedCheckbox"></input></td></tr>
                        <tr><td>Merged in Upstream</td><td><input type="checkbox" v-model="mergedIntoTeiCheckbox"></input></td></tr>
                    </div>
                    <tr><td>Flags:</td><td><input v-model="flagsInput"></input></td></tr>
                    <a @click.preventDefault="cancelEditFlags()">Cancel</a>
                </fieldset>
            </div>
            <div v-else>
                <a @click.prevent="setShowEditFlags(true)">Edit Flags</a>
            </div>
            <br>
            <tr><td>Comment:</td><td><textarea v-model="commentTextarea"></textarea></td></tr>
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
         return { showEditFlagsBox: false, editEntryComponent: "", oldEntry: "", commentTextarea: "", flagsInput: "", approvedCheckbox: "", mergedIntoTeiCheckbox: "", dict: "", groupId: "foo"}; },

     methods: {

         submitPatch() {
             if (!this.$store.state.userName) {
                 window.alert("You have to be logged in to submit patches or comments!");
                 return
             }

             if (!this.showEditEntryBox && !this.showEditFlagsBox && this.commentTextarea == "") {
                 window.alert("Your comment is empty!");
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
         },

         setShowEditFlags(boolean) {
             this.showEditFlagsBox = boolean;
         },

         cancelEditFlags() {
             if (this.previousItem.hasOwnProperty('flags')) {
                 this.flagsInput = this.previousItem.flags;
             }
             else {
                 this.flagsInput = "";
             }
             this.mergedIntoTeiCheckbox = false;
             this.approvedCheckbox = false;
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
             }
             this.editEntryComponent=this.dict.replace("_","-")+"-edit-entry";

         }
     },

     computed: mapState([
         'newEntry',
         'newEntryKeywords',
         'showEditEntryBox',
         'userRole'
     ])
 }
</script>
