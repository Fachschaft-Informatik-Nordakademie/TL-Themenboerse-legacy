import { Button } from '@material-ui/core';
import Head from 'next/head';
import React from 'react';
import styles from '../styles/Home.module.css';
import Link from 'next/link';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { fetchUser } from '../src/server/fetchUser';
import { User } from '../src/types/user';
import axiosClient from '../src/api';
import { useRouter } from 'next/router';

type Props = {
  user: User | null;
};

export async function getServerSideProps(context: GetServerSidePropsContext): Promise<GetServerSidePropsResult<Props>> {
  const user = await fetchUser(context.req.cookies);

  return {
    props: { user },
  };
}

export default function Home({ user }: Props): JSX.Element {
  const router = useRouter();

  const logout = async (): Promise<void> => {
    await axiosClient.post('/logout', {});
    await router.push('/');
  };

  return (
    <div className={styles.container}>
      <Head>
        <title>Themenbörse</title>
      </Head>

      <main className={styles.main}>
        <h1 className={styles.title}>Willkommen auf der Themenbörse!</h1>

        {user && <h3>Du bist eingeloggt als {user.email}</h3>}

        {user ? (
          <div className={styles.grid}>
            <Link href="/topic/create">
              <Button>Thema erstellen</Button>
            </Link>
            <Link href={`/user/${user.id}`}>
              <Button>Profil</Button>
            </Link>
            <Button onClick={logout}>Logout</Button>
          </div>
        ) : (
          <div className={styles.grid}>
            <Link href="/login">
              <Button>Login</Button>
            </Link>
            <Link href="/registration">
              <Button>Registrieren</Button>
            </Link>
          </div>
        )}
      </main>
    </div>
  );
}
