import React, { useState, useEffect } from 'react';
import { Navigation } from './components/Navigation';
import { SurfPage } from './components/SurfPage';
import { PersonalizedSurfPage } from './components/PersonalizedSurfPage';
import { SubmitPage } from './components/SubmitPage';
import { ImportPage } from './components/ImportPage';
import { AnalyticsPage } from './components/AnalyticsPage';
import { Toaster } from './components/ui/sonner';

export default function App() {
  const [currentPage, setCurrentPage] = useState('surf');
  const [darkMode, setDarkMode] = useState(false);

  // Initialize dark mode from localStorage or system preference
  useEffect(() => {
    const savedTheme = localStorage.getItem('theme');
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme === 'dark' || (!savedTheme && systemDark)) {
      setDarkMode(true);
      document.documentElement.classList.add('dark');
    }
  }, []);

  const toggleDarkMode = () => {
    const newDarkMode = !darkMode;
    setDarkMode(newDarkMode);
    
    if (newDarkMode) {
      document.documentElement.classList.add('dark');
      localStorage.setItem('theme', 'dark');
    } else {
      document.documentElement.classList.remove('dark');
      localStorage.setItem('theme', 'light');
    }
  };

  const renderCurrentPage = () => {
    switch (currentPage) {
      case 'surf':
        return <PersonalizedSurfPage />;
      case 'submit':
        return <SubmitPage />;
      case 'import':
        return <ImportPage />;
      case 'analytics':
        return <AnalyticsPage />;
      default:
        return <PersonalizedSurfPage />;
    }
  };

  return (
    <div className="min-h-screen bg-background">
      <Navigation
        currentPage={currentPage}
        onPageChange={setCurrentPage}
        darkMode={darkMode}
        onToggleDarkMode={toggleDarkMode}
      />
      
      <main className="pb-8">
        {renderCurrentPage()}
      </main>

      <Toaster />
    </div>
  );
}