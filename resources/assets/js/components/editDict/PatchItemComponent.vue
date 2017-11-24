<template>
    <div class="patch-item">
        <span v-if="!uselessPatchItem">
            <table border="1">
                <tr v-if="item.creation_date"><td>Date:</td><td>{{item.creation_date}}</td></tr>
                <tr v-if="item.user_name"><td>By user:</td><td>{{item.user_name}}</td></tr>
                <tr v-if="item.new_entry != item.old_entry">
                    <td>Changed Entry:</td>
                    <td><component v-bind:is="showEntryComponent" :entry="item.new_entry"></component><br>
                        <component v-bind:is="showEntryComponent" :entry="item.old_entry"></component></td>
                </td></tr>
                <tr v-if="item.approved && item.old_entry == item.new_entry">
                    <td>Entry</td><td><component v-bind:is="showEntryComponent" :entry="item.new_entry"></component></td>
                </tr>
                <tr v-if="item.approved && item.old_flags == item.new_flags">
                    <td>Flags</td><td>{{item.new_flags}}</td>
                </tr>

                <tr v-if="item.new_flags != item.old_flags">
                    <td>Changed Flags:</td>
                    <td>{{item.new_flags}}<br>
                        {{item.old_flags}}</td>
                    <tr v-if="item.comment"><td>Comment:</td><td>{{item.comment}}</td></tr>
                    <tr v-if="item.approved"><td>Set Approved</td><td>yes</td></tr>
                    <tr v-if="item.merged_into_tei"><td>Merged into upstream</td><td>yes</td></tr>
                </tr>
            </table>
            <div v-if="!commentOnly && userRole == 'admin'">
                <div v-if="showEditPatch">
                    <fieldset>
                        <legend>Edit Patch</legend>
                        <p>Set Approved: <input v-model="item.approved" type="checkbox"></p>
                        <p>Merged into upstream: <input v-model="item.merged_into_tei" type="checkbox"></p>
                        <div v-if="item.merged_into_tei != this.oldValMergedIntoTei || item.approved != this.oldValApproved">
                            <a @click.preven="updatePatch">update patch</a>
                        </div>
                        <a @click="cancelShowEditPatch()">Cancel</a>
                    </fieldset>
                </div>
                <a v-else @click="setShowEditPatch(true)">Edit Patch</a>
            </div>
        </span>
    </div>
</template>

<script>

 import { mapState } from 'vuex';

 export default {
     props: ["item"],

     data() { return { commentOnly: false, showEntryComponent: "", oldValApproved: "", oldValMergedIntoTei: "", oldValFlags: "", uselessPatchItem: false, showEditPatch: false} },

     methods: {
         updatePatch() {
             var data = {
                 dict:this.selectedDict,
                 patchId:this.item.id,
                 approved:this.item.approved,
                 mergedIntoTei:this.item.merged_into_tei,
             };
             axios.post('/submitPatchUpdate/',
                        data
             ).then(function (response) {
                 location.reload();
             });
         },

         setShowEditPatch(boolean) {
             this.showEditPatch = boolean;
         },
         cancelShowEditPatch() {
             this.showEditPatch = false;
             this.item.approved = this.oldValApproved;
             this.item.merged_into_tei = this.oldValMergedIntoTei;
         }

     },

     mounted() {
         this.showEntryComponent=this.selectedDict.replace("_","-")+"-show-entry";
         this.oldValApproved = this.item.approved;
         this.oldValMergedIntoTei = this.item.merged_into_tei;
         this.oldValFlags = this.item.flags;
         if (this.item.old_entry == this.item.new_entry && this.item.old_flags == this.item.new_flags && !this.item.approved && !this.item.merged_into_tei)
             this.commentOnly = true;
         if (this.commentOnly && this.item.comment == "")
             this.uselessPatchItem = true;

     },

     computed: mapState([
         'selectedDict',
         'userRole'
     ]),

 }
</script>
