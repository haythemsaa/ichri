import type { Metadata } from 'next'
import './globals.css'

export const metadata: Metadata = {
  title: 'ichri.tn - Plateforme B2B pour Commerces de Proximité',
  description: 'Plateforme B2B tunisienne pour l\'approvisionnement des épiceries et commerces de proximité',
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="fr">
      <body>{children}</body>
    </html>
  )
}
