// Autocomplétion pour la recherche de magazines
function initMagazineAutocomplete(inputSelector, resultsSelector) {
  const input = document.querySelector(inputSelector);
  const resultsContainer = document.querySelector(resultsSelector);
  
  if (!input || !resultsContainer) return;

  let debounceTimer;

  input.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    const query = this.value.trim();

    if (query.length < 2) {
      resultsContainer.innerHTML = '';
      resultsContainer.style.display = 'none';
      return;
    }

    debounceTimer = setTimeout(() => {
      fetch(`/api/magazines/autocomplete?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
          if (data.length === 0) {
            resultsContainer.innerHTML = '<div class="autocomplete-item autocomplete-empty">Aucun magazine trouvé</div>';
            resultsContainer.style.display = 'block';
            return;
          }

          resultsContainer.innerHTML = data.map(magazine => `
            <a href="${magazine.url}" class="autocomplete-item">
              <div class="autocomplete-title">${highlightMatch(magazine.title, query)}</div>
              <div class="autocomplete-desc">${magazine.description || ''}</div>
            </a>
          `).join('');
          resultsContainer.style.display = 'block';
        })
        .catch(error => {
          console.error('Erreur autocomplete:', error);
          resultsContainer.innerHTML = '';
          resultsContainer.style.display = 'none';
        });
    }, 300); // Attendre 300ms après la dernière frappe
  });

  // Fermer les suggestions en cliquant ailleurs
  document.addEventListener('click', function(e) {
    if (!input.contains(e.target) && !resultsContainer.contains(e.target)) {
      resultsContainer.style.display = 'none';
    }
  });
}

// Autocomplétion pour la recherche d'articles
function initArticleAutocomplete(inputSelector, resultsSelector) {
  const input = document.querySelector(inputSelector);
  const resultsContainer = document.querySelector(resultsSelector);
  
  if (!input || !resultsContainer) return;

  let debounceTimer;

  input.addEventListener('input', function() {
    clearTimeout(debounceTimer);
    const query = this.value.trim();

    if (query.length < 2) {
      resultsContainer.innerHTML = '';
      resultsContainer.style.display = 'none';
      return;
    }

    debounceTimer = setTimeout(() => {
      fetch(`/api/articles/autocomplete?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
          if (data.length === 0) {
            resultsContainer.innerHTML = '<div class="autocomplete-item autocomplete-empty">Aucun article trouvé</div>';
            resultsContainer.style.display = 'block';
            return;
          }

          resultsContainer.innerHTML = data.map(article => `
            <div class="autocomplete-item">
              <div class="autocomplete-title">${highlightMatch(article.title, query)}</div>
              <div class="autocomplete-meta">
                <span class="autocomplete-author">${article.auteur}</span>
                <span class="autocomplete-separator">•</span>
                <span class="autocomplete-magazine">${article.magazine}</span>
                ${article.date ? `<span class="autocomplete-separator">•</span><span class="autocomplete-date">${article.date}</span>` : ''}
              </div>
            </div>
          `).join('');
          resultsContainer.style.display = 'block';
        })
        .catch(error => {
          console.error('Erreur autocomplete:', error);
          resultsContainer.innerHTML = '';
          resultsContainer.style.display = 'none';
        });
    }, 300);
  });

  // Fermer les suggestions en cliquant ailleurs
  document.addEventListener('click', function(e) {
    if (!input.contains(e.target) && !resultsContainer.contains(e.target)) {
      resultsContainer.style.display = 'none';
    }
  });
}

// Fonction pour surligner le texte correspondant
function highlightMatch(text, query) {
  if (!query) return text;
  const regex = new RegExp(`(${query})`, 'gi');
  return text.replace(regex, '<strong>$1</strong>');
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
  // Pour la page magazines
  initMagazineAutocomplete('#magazine-search-input', '#magazine-autocomplete-results');
  
  // Pour la page de recherche d'articles
  initArticleAutocomplete('#article-search-input', '#article-autocomplete-results');
  
  // Pour la page de détail d'un magazine (filtre d'articles)
  initArticleAutocomplete('#magazine-article-search-input', '#magazine-article-autocomplete-results');
});
