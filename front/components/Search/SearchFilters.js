import React, { PropTypes } from 'react';

export default class SearchFilters extends React.Component {
    render() {
        return (
            <div className="search__bar background--blue space--30 text--white text--body">
                <div className="search__bar__options">
                    <input className="search-box" placeholder="Recherche" />

                    <div>
                        dans un rayon de
                        <select defaultValue={100}>
                            <option value={5}>5</option>
                            <option value={10}>10</option>
                            <option value={25}>25</option>
                            <option value={50}>50</option>
                            <option value={100}>100</option>
                            <option value={150}>150</option>
                        </select>
                        autour de
                        <input className="search-city" placeholder="Ville ou code postal" />
                    </div>
                </div>

                <ul className="search__bar__toggle">
                    <li className={this.props.active === 'events' ? 'active' : ''}>
                        <a href="/evenements" className="link--no-decor">Événements</a>
                    </li>
                    <li className={this.props.active === 'committees' ? 'active' : ''}>
                        <a href="/comites" className="link--no-decor">Comités</a>
                    </li>
                </ul>
            </div>
        );
    }
}

SearchFilters.propTypes = {
    active: PropTypes.string.isRequired
};
