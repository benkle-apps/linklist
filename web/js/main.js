/*
 * Copyright (c) 2017 Benjamin Kleiner
 *
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.  IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

$(() => {
    let Mapped = function (mappedObject) {
        for (let element in mappedObject) {
            this[element] = ko.observable(mappedObject[element]);
        }
    };

    let viewModel = {
        urlFilter: ko.observable(''),
        domainFilter: ko.observable(''),
        domains: ko.observableArray([])
            .extend({
                filtered: (domain) => {
                    let filter = viewModel.domainFilter().trim();
                    return !filter || domain.url().indexOf(filter) > -1;
                },
                paged: {pageSize: 10}
            }),
        urls: ko.observableArray([])
            .extend({
                filtered: (url) => {
                    let filter = viewModel.urlFilter().trim();
                    return !filter || url.url().indexOf(filter) > -1;
                },
                paged: {pageSize: 10}
            }),
        total: ko.observable(0),
        currentDomain: ko.observable(false),
        loadUrls: (domain, event) => {
            event.preventDefault();
            $
                .getJSON(domain.url())
                .then((result) => {
                    history.pushState(result, domain.url(), domain.url());
                    viewModel.setData(result);
                });
        },
        visitUrl: (url, event) => {
            if (event.which === 1) {
                if (url.gone() || url.deleted()) {
                    event.preventDefault();
                    return;
                }
                event.preventDefault();
                window.open(url.url(), '_blank');
                $.getJSON(url.deleteAction(), () => {
                    url.deleted(true);
                });
            }
        },
        setData: (data) => {
            viewModel.domains.source(data.domains.map((domain) => new Mapped(domain)));
            viewModel.urls.source(data.urls.map((url) => new Mapped(url)));
            viewModel.urls.page(0);
            viewModel.currentDomain(data.currentDomain);
            viewModel.total(data.total);
        }
    };

    viewModel.setData(data);

    $(window).on('popstate', (event) => {
        viewModel.setData(event.originalEvent.state);
    });

    ko.applyBindings(viewModel);
});
