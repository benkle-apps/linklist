/* This Source Code Form is subject to the terms of the MIT License.
 * If a copy of the MIT was not distributed with this
 * file, You can obtain one at http://opensource.org/licenses/MIT .
 */

/**
 * paged - Pages an observable array.
 * Most fields and methods are easy, but `pages` requires a bit of an
 * explaination. It won't just return a list of numbers, but instead of objects,
 * each representing a page. They ain't all equally build, but all contain at least
 * * `canGoto` is a boolean telling wether it's an actually page (that you can go to), or not
 * * `isCurrent` is a boolean telling you wether this is the current page.
 * * `humanreadable` is a humanreadable string, e.g. the ellipsis text, or the page number in humanfriendly numbering (starting at 1).
 * Proper pages also contain `pageNr`, which is the computerfriendly page number (starting at 0), and `goto`, a function that
 * tells the paged observable to directly jump to that page, updating everything.
 * @demo
 * @param options The page size, or an options hash.
 * @param options.pageSize The page size.
 * @param options.format An array or hash containing formatting instructions for the pages list. @see ko.extenders.paged.format
 * @field page An observable containing the current page number.
 * @field pageSize An observable containing the page size.
 * @field pageCount A computed returning the current number of pages.
 * @field format An observable containing the format instructions hash for the pages list.
 * @field source The original observable containing the raw data.
 * @field parentObservable If used with other extenders, this points to the previously declared extension.
 * @method nextPage Moves to next page, if possible.
 * @method previousPage Moves to previous page, if possible.
 * @method hasNextPage Checks wether there is a next page.
 * @method hasPreviousPage Checks weather there is a previous page.
 * @field pages A computed returning a list of objects to produce a page navigation. @see ko.extenders.paged.format.doFormat
 */
ko.extenders.paged = function (source, options) {
    if (typeof options != 'object')
        options = {pageSize: options, format: false};
    if (options.format instanceof Array)
        options.format = ko.extenders.paged.format.apply(undefined, options.format);
    if (!ko.isObservable(source))
        source = ko.observableArray(source);
    var pageSize = ko.observable(options.pageSize);
    var pageCount = ko.computed(function () {
        return Math.ceil(source().length / pageSize());
    });
    var pageNr = ko.observable(0);
    var page = ko.computed({
        read: function () {
            return Math.max(0, Math.min(pageNr(), pageCount()));
        },
        write: function (nv) {
            pageNr(nv);
        }
    });
    var self = ko.computed(function () {
        return source().slice(page() * pageSize(), (page() + 1) * pageSize());
    });

    self.page = page;
    self.pageSize = pageSize;
    self.pageCount = pageCount;
    self.format = ko.observable(options.format);
    self.source = source.source ? source.source : source;
    self.parentObservable = source;

    self.nextPage = function () {
        self.page(self.page() + 1);
    };
    self.previousPage = function () {
        self.page(self.page() - 1);
    };

    self.hasNextPage = ko.computed(function () {
        return self.page() < self.pageCount() - 1;
    });
    self.hasPreviousPage = ko.computed(function () {
        return self.page() > 0;
    });

    self.pages = ko.computed(function () {
        var result = [];
        for (var i = 0; i < self.pageCount(); i++)
            result.push({
                pageNr: i,
                humanreadable: i + 1,
                isCurrent: i == self.page(),
                canGoto: true,
                goto: function () {
                    self.page(this.pageNr);
                }
            });
        if (self.format()) {
            result = ko.extenders.paged.format.doFormat(result, self.format(), self.page());
        }
        return result;
    });

    return self;
};

/**
 * Creates a format hash used for the pages field. Note that `paged` maps
 * an array to this function to make the extension declaration a little simpler.
 * @param start The number of pages to be show from the beginning.
 * @param beforeWindowEllipsis A string for the ellipsis between the start and the current window.
 * @param windowSize The size of the window arround the current page.
 * @param afterWindowEllipsis A string for the ellipsis between the current window and the end.
 * @param end The number of pages to be show from the end.
 */
ko.extenders.paged.format = function (start, beforeWindowEllipsis, windowSize, afterWindowEllipsis, end) {
    return {
        start: start,
        beforeWindowEllipsis: beforeWindowEllipsis,
        windowSize: windowSize,
        afterWindowEllipsis: afterWindowEllipsis,
        end: end
    };
};

/**
 * Internal. Produces a nonpage. Used for the ellipsis.
 * @param text The text of the nonpage.
 */
ko.extenders.paged.format.nonPage = function (text) {
    return {
        canGoto: false,
        isCurrent: false,
        humanreadable: text
    };
};

/**
 * Internal. Used to check wether two intervals overlap, and produces a union interval if required.
 * @param l1 Lower bound of the first interval.
 * @param u1 Upper bound of the first interval.
 * @param l2 Lower bound of the second interval.
 * @param u2 Upper bound of the second interval.
 * @param bool Wether the function should only check if the intervals overlap (true), or return the union interval, if any (false).
 */
ko.extenders.paged.format.rangeOverlap = function (l1, u1, l2, u2, bool) {
    if (u1 >= l2)
        return bool ? true : {l: Math.min(l1, l2), u: Math.max(u1, u2)};
    else
        return false;
}

/**
 * Internal. Produces a formatted list of pages.
 * @param array The array of pages.
 * @param format The format hash as produced by `ko.extenders.paged.format`.
 * @param current The current page number.
 */
ko.extenders.paged.format.doFormat = function (array, format, current) {
    var winPre = 0;
    var winAft = 0;
    var ro = ko.extenders.paged.format.rangeOverlap;
    if (format.windowSize % 2) {
        winPre = winAft = (format.windowSize - 1) / 2;
    } else {
        winAft = format.windowSize / 2;
        winPre = winAft - 1;
    }
    var start = format.start;
    var end = array.length - format.end;
    winPre = Math.max(0, current - winPre);
    winAft = Math.min(array.length - 1, current + winAft + 1);
    if ((ro(0, start, winPre, winAft, true) && ro(winPre, winAft, end, array.length, true)) || ro(0, start, end, array.length, true))
        return array;

    var p = false;
    var result = [];
    if (p = ro(0, start, winPre, winAft)) {
        result.push.apply(result, array.slice(0, p.u));
        result.push(ko.extenders.paged.format.nonPage(format.afterWindowEllipsis));
        result.push.apply(result, array.slice(end));
    } else if (p = ro(winPre, winAft, end, array.length)) {
        result.push.apply(result, array.slice(0, start));
        result.push(ko.extenders.paged.format.nonPage(format.beforeWindowEllipsis));
        result.push.apply(result, array.slice(p.l));
    } else {
        result.push.apply(result, array.slice(0, start));
        result.push(ko.extenders.paged.format.nonPage(format.beforeWindowEllipsis));
        result.push.apply(result, array.slice(winPre, winAft));
        result.push(ko.extenders.paged.format.nonPage(format.afterWindowEllipsis));
        result.push.apply(result, array.slice(end));
    }
    return result;
};
