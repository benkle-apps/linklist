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
    let viewModel = {
        filter: ko.observable(''),
        domains: ko.observableArray([])
            .extend({
                paged: {pageSize: 10}
            }),
        urls: ko.observableArray([])
            .extend({
                filtered: (url) => {
                    return !viewModel.filter().trim() || url.url.indexOf(viewModel.filter()) > -1;
                },
                paged: {pageSize: 10}
            }),
        total: ko.observable(0),
        currentDomain: ko.observable(false),
        loadLinks: (domain) => {
            $
                .getJSON(domain.url)
                .then((result) => {
                    history.pushState(result, domain.url, domain.url);
                    viewModel.setData(result);
                });
        },
        setData: (data) => {
            viewModel.domains.source(data.domains);
            viewModel.urls.source(data.urls);
            viewModel.urls.page(0);
            viewModel.currentDomain(data.currentDomain);
            viewModel.total(data.total);
            viewModel.filter('');
        }
    };

    viewModel.setData(data);

    $(window).on('popstate', (event) => {
        viewModel.setData(event.originalEvent.state);
    });

    ko.applyBindings(viewModel);
});
