import { User } from '../../src/types/user';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { fetchUser } from '../../src/server/fetchUser';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import { Chip, Typography } from '@material-ui/core';
import { useRouter } from 'next/router';
import { useEffect, useState } from 'react';
import { Topic } from '../../src/types/topic';
import axiosClient from '../../src/api';
import { makeStyles } from '@material-ui/core/styles';
import { PageComponent } from '../../src/types/PageComponent';

type Props = {
  user: User;
};

export async function getServerSideProps(context: GetServerSidePropsContext): Promise<GetServerSidePropsResult<Props>> {
  const user = await fetchUser(context.req.cookies);

  if (user === null) {
    return { redirect: { destination: '/login', permanent: false } };
  }

  return {
    props: { user, ...(await serverSideTranslations('de', ['common', 'topic-creation'])) },
  };
}

const useStyles = makeStyles((theme) => ({
  section: {
    marginTop: theme.spacing(2),
  },
  tags: {
    marginLeft: theme.spacing(-1),
  },
  tagItem: { marginLeft: theme.spacing(1) },
}));

const TopicDetail: PageComponent<void> = (): JSX.Element => {
  const router = useRouter();
  const classes = useStyles();
  const topicId = router.query.view as string;

  const [topic, setTopic] = useState<Topic>();
  const [loading, setLoading] = useState<boolean>(true);

  const fetchTopic = async (): Promise<void> => {
    setLoading(true);
    const response = await axiosClient.get('/topic/' + topicId);
    setTopic(response.data);
    setLoading(false);
  };

  useEffect(() => {
    fetchTopic();
  }, []);

  if (loading) {
    return <div>Wird geladen</div>;
  }

  return (
    <div>
      <Typography gutterBottom variant="h4" component="h2">
        Thema #{topicId}: {topic.title}
      </Typography>
      <div>
        <Typography gutterBottom variant="h5" component="h3">
          Beschreibung
        </Typography>
        <Typography variant="body1" component="p">
          {topic.description}
        </Typography>
      </div>

      <div className={classes.section}>
        <Typography gutterBottom variant="h5" component="h3">
          Anforderungen
        </Typography>
        <Typography variant="body1" component="p">
          {topic.requirements}
        </Typography>
      </div>
      {topic.website && topic.website.length > 0 && (
        <div className={classes.section}>
          <Typography gutterBottom variant="h5" component="h3">
            Website
          </Typography>
          <Typography variant="body1" component="p">
            <a href={topic.website} target="_blank" rel="noopener noreferrer">
              {topic.website}
            </a>
          </Typography>
        </div>
      )}
      <div className={classes.section}>
        <Typography gutterBottom variant="h5" component="h3">
          Anzahl Seiten
        </Typography>
        <Typography variant="body1" component="p">
          {topic.pages}
        </Typography>
      </div>
      <div className={classes.section}>
        <Typography gutterBottom variant="h5" component="h3">
          Tags
        </Typography>
        <Typography variant="body1" component="p">
          <span className={classes.tags}>
            {topic.tags.map((t) => (
              <Chip classes={{ root: classes.tagItem }} key={t} label={t} />
            ))}
          </span>
        </Typography>
      </div>
    </div>
  );
};

TopicDetail.layout = 'main';

export default TopicDetail;
