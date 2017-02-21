import React, { PropTypes } from 'react';
import SearchFilters from './SearchFilters';

export default class SearchCommittees extends React.Component {
    render() {
        return (
            <div>
                <SearchFilters
                    active="committees"
                />

                <div className="search__results app_search_committees"></div>
            </div>
        );
    }
}
