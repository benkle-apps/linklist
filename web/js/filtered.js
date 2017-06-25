/* This Source Code Form is subject to the terms of the MIT License.
 * If a copy of the MIT was not distributed with this
 * file, You can obtain one at http://opensource.org/licenses/MIT .
 */

/**
 * filtered - Filters an observable array.
 * @demo
 * @param options A filtering function, or an options hash.
 * @param options.filterFunction A filtering function.
 * @field filterFunction An observable containing the filtering function for later changes.
 * @field source The original observable containing the raw data.
 * @field parentObservable If used with other extenders, this points to the previously declared extension.
 */
ko.extenders.filtered = function (source, options) {
    if (typeof options != 'object')
        options = {filterFunction: options};
    var filter = ko.observable(options.filterFunction);
    var self = ko.computed(function () {
        return ko.utils.arrayFilter(source(), filter());
    });
    self.filterFunction = filter;
    self.source = source.source ? source.source : source;
    self.parentObservable = source;
    return self;
};
