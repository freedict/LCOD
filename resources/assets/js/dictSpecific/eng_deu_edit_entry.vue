<template>
    <div class="edit-entry">
        <div v-if="showEditEntryBox">
            <form>
                <fieldset >
                    <legend>Edit Entry</legend>
                    <div class="headword">
                        <b>Headword</b><br>
                        Orthographie: <input v-model="parsedEntry.headword.quoteOrOrth">
                        Pron: <input v-model="parsedEntry.headword.pron">
                    </div>
                    <div class="senses" v-for="(sense, senseId) in parsedEntry.senses">
                        <div class="transes" v-for="(trans, transId) in sense.transes">
                            <div class="trans">
                                <b>Translation {{transId+1}}</b><br>
                                Orthographie: <input v-model="trans.quoteOrOrth">
                                Gender: <select v-model="trans.gen">
                                    <option>f</option>
                                    <option>n</option>
                                    <option>m</option>
                                    <option></option>
                                </select>
                                Usg: <input v-model="trans.usg" style="width: 70px">
                                Num: <select v-model="parsedEntry.headword.num">
                                    <option>s</option>
                                    <option>pl</option>
                                    <option></option>
                                </select>
                                Pos: <select v-model="parsedEntry.headword.pos">
                                    <option>n</option>
                                    <option></option>
                                </select>
                                <a @click.preventDefault="deleteTrans(senseId, transId)">Delete</a>
                            </div>
                        </div>
                        <a @click.preventDefault="addTrans(senseId)">Add Tranlsation</a><br>
                        <a @click.preventDefault="cancelEditEntry()">Cancel</a>
                    </div>
                </fieldset>
            </form>
        </div>
        <div v-else>
            <a @click.preventDefault="setShowEditEntryBox(true)">Edit Entry</a>
        </div>
    </div>
</template>
<script>

 import * as parser from './eng_deu_parse.js';

export default {
     props: ['entry'],

     data() {
         return { groupId: "foo", parsedEntry: "" , showEditEntryBox: false}; },

     created() {
         this.parsedEntry = parser.parse(this.entry);
     },

     watch: {
         parsedEntry: {
             handler(val) {
                 this.$store.commit("setNewEntry", parser.parsedEntry2tei(this.parsedEntry));
                 this.$store.commit("setNewEntryKeywords" , parser.extractKeywords(this.parsedEntry));
             },
             deep: true
         }
     },

     methods: {

         deleteTrans(senseId, transId) {
             this.parsedEntry.senses[senseId].transes.splice(transId, 1);
         },

         addTrans(senseId) {
            this.parsedEntry.senses[senseId].transes.push(new parser.Trans());
         },

         setShowEditEntryBox(boolean) {
             this.showEditEntryBox = boolean;
         },

         cancelEditEntry() {
             this.parsedEntry = parser.parse(this.entry);
             this.showEditEntryBox = false;
         },
     }
 }
</script>
<style>
 fieldset
 {
     border: groove 1.5px #e5e6e6
 }
</style>
