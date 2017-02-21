import React from 'react';
import { render } from 'react-dom';
import SearchCommittees from '../components/Search/SearchCommittees';

/*
 * Search committees
 */
export default (defaultLocation) => {
    render(
        <SearchCommittees />,
        dom('#search-engine')
    );
};
