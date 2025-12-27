import React from 'react';
import { Link } from 'react-router-dom';
import { createPageUrl } from './utils';
import { Bus, ArrowLeft, Home } from 'lucide-react';

export default function Layout({ children, currentPageName }) {
  return (
    <div className="min-h-screen bg-gradient-to-br from-amber-50 via-white to-amber-50">
      <style>{`
        :root {
          --primary-yellow: #F59E0B;
          --primary-yellow-dark: #D97706;
          --primary-yellow-light: #FEF3C7;
        }
      `}</style>
      
      {children}
    </div>
  );
}