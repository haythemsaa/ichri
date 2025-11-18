'use client';

import { useState } from 'react';

export default function DashboardPage() {
  const [stats] = useState({
    totalGrocers: 2847,
    activeOrders: 143,
    todayRevenue: 45678.50,
    monthlyGMV: 1234567.89,
  });

  return (
    <div className="min-h-screen bg-gray-100">
      <div className="bg-white shadow">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="py-6">
            <h1 className="text-3xl font-bold text-gray-900">
              Dashboard ichri.tn
            </h1>
            <p className="mt-1 text-sm text-gray-600">
              Vue d'ensemble de la plateforme
            </p>
          </div>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {/* Total Grocers */}
          <div className="bg-white rounded-lg shadow p-6">
            <div className="flex items-center">
              <div className="flex-1">
                <p className="text-sm font-medium text-gray-600">
                  Épiciers Actifs
                </p>
                <p className="mt-1 text-3xl font-semibold text-gray-900">
                  {stats.totalGrocers.toLocaleString()}
                </p>
              </div>
              <div className="ml-4">
                <div className="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                  <span className="text-2xl">🏪</span>
                </div>
              </div>
            </div>
            <div className="mt-4">
              <span className="text-sm text-green-600">+12.5%</span>
              <span className="text-sm text-gray-600 ml-2">vs mois dernier</span>
            </div>
          </div>

          {/* Active Orders */}
          <div className="bg-white rounded-lg shadow p-6">
            <div className="flex items-center">
              <div className="flex-1">
                <p className="text-sm font-medium text-gray-600">
                  Commandes Actives
                </p>
                <p className="mt-1 text-3xl font-semibold text-gray-900">
                  {stats.activeOrders}
                </p>
              </div>
              <div className="ml-4">
                <div className="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                  <span className="text-2xl">📦</span>
                </div>
              </div>
            </div>
            <div className="mt-4">
              <span className="text-sm text-blue-600">En livraison</span>
            </div>
          </div>

          {/* Today Revenue */}
          <div className="bg-white rounded-lg shadow p-6">
            <div className="flex items-center">
              <div className="flex-1">
                <p className="text-sm font-medium text-gray-600">
                  Revenus Aujourd'hui
                </p>
                <p className="mt-1 text-3xl font-semibold text-gray-900">
                  {stats.todayRevenue.toLocaleString('fr-TN', {
                    style: 'currency',
                    currency: 'TND',
                  })}
                </p>
              </div>
              <div className="ml-4">
                <div className="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                  <span className="text-2xl">💰</span>
                </div>
              </div>
            </div>
            <div className="mt-4">
              <span className="text-sm text-green-600">+8.3%</span>
              <span className="text-sm text-gray-600 ml-2">vs hier</span>
            </div>
          </div>

          {/* Monthly GMV */}
          <div className="bg-white rounded-lg shadow p-6">
            <div className="flex items-center">
              <div className="flex-1">
                <p className="text-sm font-medium text-gray-600">
                  GMV Mensuel
                </p>
                <p className="mt-1 text-3xl font-semibold text-gray-900">
                  {(stats.monthlyGMV / 1000000).toFixed(2)}M
                </p>
              </div>
              <div className="ml-4">
                <div className="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                  <span className="text-2xl">📈</span>
                </div>
              </div>
            </div>
            <div className="mt-4">
              <span className="text-sm text-purple-600">+18.7%</span>
              <span className="text-sm text-gray-600 ml-2">vs mois dernier</span>
            </div>
          </div>
        </div>

        {/* Recent Orders */}
        <div className="mt-8 bg-white rounded-lg shadow">
          <div className="px-6 py-4 border-b border-gray-200">
            <h2 className="text-lg font-semibold text-gray-900">
              Commandes Récentes
            </h2>
          </div>
          <div className="p-6">
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Commande
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Épicier
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Montant
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Statut
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Date
                    </th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  <tr>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      #ICH2024-000143
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      Épicerie Essalem
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      285.500 TND
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                        En livraison
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      Aujourd'hui 14:23
                    </td>
                  </tr>
                  <tr>
                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      #ICH2024-000142
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      Mini Market Said
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      412.750 TND
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                        Livrée
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      Aujourd'hui 13:45
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
