import Link from 'next/link';

export default function HomePage() {
  return (
    <div className="min-h-screen bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500">
      <div className="flex flex-col items-center justify-center min-h-screen px-4">
        <div className="text-center">
          <h1 className="text-6xl font-bold text-white mb-4">
            ichri.tn
          </h1>
          <p className="text-2xl text-white/90 mb-8">
            Plateforme B2B pour Commerces de Proximité en Tunisie
          </p>
          <div className="flex gap-4 justify-center">
            <Link
              href="/dashboard"
              className="px-8 py-4 bg-white text-indigo-600 rounded-lg font-semibold hover:bg-gray-100 transition"
            >
              Dashboard Admin
            </Link>
            <a
              href="https://github.com/haythemsaa/ichri"
              target="_blank"
              rel="noopener noreferrer"
              className="px-8 py-4 bg-transparent border-2 border-white text-white rounded-lg font-semibold hover:bg-white/10 transition"
            >
              GitHub
            </a>
          </div>
        </div>

        <div className="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl">
          <div className="bg-white/10 backdrop-blur-lg rounded-lg p-6 text-white">
            <h3 className="text-xl font-semibold mb-2">🛒 Catalogue Digital</h3>
            <p className="text-white/80">
              1000+ produits FMCG disponibles en quelques clics
            </p>
          </div>
          <div className="bg-white/10 backdrop-blur-lg rounded-lg p-6 text-white">
            <h3 className="text-xl font-semibold mb-2">🚚 Livraison Rapide</h3>
            <p className="text-white/80">
              Livraison gratuite en moins de 24h partout en Tunisie
            </p>
          </div>
          <div className="bg-white/10 backdrop-blur-lg rounded-lg p-6 text-white">
            <h3 className="text-xl font-semibold mb-2">💳 Crédit Flexible</h3>
            <p className="text-white/80">
              Accès au crédit jusqu'à 30 jours pour améliorer la trésorerie
            </p>
          </div>
        </div>

        <div className="mt-16 text-center text-white/80">
          <p className="text-sm">
            Objectif Année 1: 5,000 épiceries actives • 30M TND GMV
          </p>
          <p className="text-xs mt-2">
            © 2024 ichri.tn - Fait avec ❤️ en Tunisie 🇹🇳
          </p>
        </div>
      </div>
    </div>
  );
}
