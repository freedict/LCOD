
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
// bootstrap
require('./bootstrap');

// jquery ui widget autocomplete
import $ from 'jquery';
window.$ = window.jQuery = $;
import 'jquery-ui/ui/widgets/autocomplete.js';
$('#autocomplete').autocomplete();

// vue
window.Vue = require('vue');

// vuex
import Vuex from 'vuex';
Vue.use(Vuex);
const store = new Vuex.Store({
    state: {
        selectedDict: "",
        lookupResult: [],
        lookupPatchGroupResult: [],
        searchTerm: "",
        groupId: "",
        newEntry: [],
        didALookup: false
    },
    mutations: {
        setSelectedDict (state, dict) {
            state.selectedDict = dict;
        },
        setGroupId(state, groupId) {
            state.groupId = groupId;
        },
        setLookupResult(state, data) {
            state.lookupResult = data;
        },
        setLookupPatchGroupResult(state, data) {
            state.lookupPatchGroupResult = data;
        },
        setNewEntry(state, data) {
            state.newEntry = data;
        },
        setNewEntryKeywords(state, data) {
            state.newEntryKeywords = data;
        },
        setDidALookup(state, data) {
            state.didALookup = data;
        }
    }
});

// vue
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
Vue.component('search-term-bar', require('./components/readDict/SearchTermBar.vue'));
Vue.component('select-dict-form', require('./components/readDict/SelectDictFormComponent.vue'));
Vue.component('search-result-box', require('./components/readDict/SearchResultBoxComponent.vue'));
Vue.component('dict-read', require('./components/readDict/DictReadComponent.vue'));

Vue.component('patch-item', require('./components/editDict/PatchItemComponent.vue'));
Vue.component('dict-edit', require('./components/editDict/DictEditComponent.vue'));
Vue.component('edit-patch-item', require('./components/editDict/EditPatchItemComponent.vue'));

Vue.component('eng-deu-edit-entry', require('./dictSpecific/eng_deu_edit_entry.vue'));
Vue.component('eng-deu-show-entry', require('./dictSpecific/eng_deu_show_entry.vue'));

const app = new Vue({
    store,
    el: '#app'
});